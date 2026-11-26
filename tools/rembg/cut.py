"""
Shopio · détourage des photos (fond retiré) avec rembg, pour le thème Lookbook.

Usage : python -I cut.py <travaux.json>
  travaux.json : [{"in": "chemin/photo.webp", "out": "dossier/storage/public/cutouts", "name": "p12-abc"}, ...]
Pour chaque photo : retire le fond, garde le sujet principal (pas les textes ni les objets autour),
recadre au plus près, puis écrit out/large/name.png, out/medium/name.png et out/thumb/name.png.
Affiche une ligne JSON par photo : {"name": ..., "ok": true} ou {"name": ..., "ok": false, "error": ...}.

Installation sur le serveur : pip install "rembg[cpu]" pillow-heif scipy
(le modèle, environ 170 Mo, est téléchargé une seule fois au premier usage).
"""
import json
import os
import sys

import numpy as np
from PIL import Image
from rembg import new_session, remove
from scipy import ndimage

try:
    import pillow_heif
    pillow_heif.register_heif_opener()
except Exception:
    pass


def main_subject(cut: Image.Image) -> Image.Image:
    """Garde le plus grand objet, les morceaux collés à lui (bouchon, étiquette) et un produit posé à côté ; retire le reste (textes, fond)."""
    a = np.array(cut)
    mask = a[:, :, 3] > 24
    lab, cnt = ndimage.label(mask)
    if not cnt:
        return cut
    sizes = ndimage.sum(mask, lab, range(1, cnt + 1))
    big = int(np.argmax(sizes)) + 1
    keep = lab == big
    ys, xs = np.where(keep)
    bx0, bx1, by1 = xs.min(), xs.max(), ys.max()
    for k in range(1, cnt + 1):
        if k == big or sizes[k - 1] < 0.02 * sizes[big - 1]:
            continue
        yk, xk = np.where(lab == k)
        if xk.min() >= bx0 - 10 and xk.max() <= bx1 + 10 and yk.max() <= by1 + 5:
            keep |= lab == k
            continue
        # Deuxième produit posé à côté (flacon devant sa boîte) : objet plein, assez grand, posé au même niveau.
        # Les textes (prix, « 100ml ») sont faits de petites lettres séparées et ne passent pas ce filtre.
        fill = sizes[k - 1] / ((xk.max() - xk.min() + 1) * (yk.max() - yk.min() + 1))
        if sizes[k - 1] >= 0.12 * sizes[big - 1] and fill >= 0.4 and abs(int(yk.max()) - int(by1)) <= 0.15 * a.shape[0]:
            keep |= lab == k
    a[~keep, 3] = 0
    return Image.fromarray(a)


def run(jobs):
    session = new_session('isnet-general-use')
    for job in jobs:
        name = job['name']
        try:
            src = Image.open(job['in'])
            src.thumbnail((1800, 1800))
            rgba = src.convert('RGBA')
            # Photo déjà sans fond (PNG transparent) : on la garde telle quelle, la redétourer l'abîmerait
            if (np.array(rgba)[:, :, 3] < 250).mean() > 0.05:
                cut = rgba
            else:
                cut = main_subject(remove(rgba.convert('RGB'), session=session, post_process_mask=True))
            box = cut.getbbox()
            if not box:
                raise ValueError('aucun sujet trouvé sur la photo')
            cut = cut.crop(box)
            pad = max(8, int(max(cut.size) * 0.03))
            canvas = Image.new('RGBA', (cut.width + 2 * pad, cut.height + 2 * pad), (0, 0, 0, 0))
            canvas.alpha_composite(cut, (pad, pad))
            for size, dim in (('large', (1100, 1500)), ('medium', (600, 820))):
                os.makedirs(os.path.join(job['out'], size), exist_ok=True)
                c = canvas.copy()
                c.thumbnail(dim)
                c.save(os.path.join(job['out'], size, name + '.png'), optimize=True)
            # Vignette carrée sur fond blanc (pastilles, listes)
            side = max(canvas.size)
            t = Image.new('RGBA', (side, side), (255, 255, 255, 255))
            t.alpha_composite(canvas, ((side - canvas.width) // 2, (side - canvas.height) // 2))
            os.makedirs(os.path.join(job['out'], 'thumb'), exist_ok=True)
            t.convert('RGB').resize((200, 200)).save(os.path.join(job['out'], 'thumb', name + '.png'))
            print(json.dumps({'name': name, 'ok': True}), flush=True)
        except Exception as e:  # une photo ratée n'arrête pas les autres
            print(json.dumps({'name': name, 'ok': False, 'error': str(e)[:200]}), flush=True)


if __name__ == '__main__':
    with open(sys.argv[1], encoding='utf-8') as f:
        run(json.load(f))
