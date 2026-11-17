<?php

namespace App\Support;

/**
 * Bouton « Importer dans Shopio » à glisser dans les favoris du navigateur (ordinateur).
 * Sur la page d'un produit (AliExpress, Alibaba, Jumia, Shopify…), un clic lit la page TELLE QUE LE VENDEUR LA VOIT
 * (nom, description, prix affiché, grandes photos, texte visible) et ouvre « Importer les photos d'un produit » avec ces
 * informations dans l'ancre de l'adresse (#import=…, jamais envoyée au serveur par le navigateur). La page n'utilise que
 * les photos : elle les envoie à products.import.photos, qui les télécharge avec les contrôles habituels.
 */
class ImportBookmarklet
{
    /** Code du bouton (sans commentaire // : il tient sur une seule ligne). */
    private const JS = <<<'JS'
(function(){
var S=__TARGET__;
var q=function(s){var e=document.querySelector(s);return e?String(e.getAttribute('content')||e.textContent||'').trim():''};
var p=null;
var f=function(o){if(!o||typeof o!=='object'||p)return;var t=o['@type'];if(t==='Product'||t==='ProductGroup'||(Array.isArray(t)&&t.indexOf('Product')>-1)){p=o;return}(Array.isArray(o)?o:(o['@graph']||[])).forEach(f)};
document.querySelectorAll('script[type="application/ld+json"]').forEach(function(s){try{f(JSON.parse(s.textContent))}catch(e){}});
var imgs=[];
var add=function(u){if(u&&typeof u==='object')u=u.url||u.contentUrl;if(typeof u!=='string'||!u)return;try{u=new URL(u,location.href).href}catch(e){return}if(!/^https?:/i.test(u))return;u=u.replace(/(\.(jpe?g|png|webp))_[^\/]*$/i,'$1');if(imgs.indexOf(u)<0)imgs.push(u)};
if(p)[].concat(p.image||[]).forEach(add);
document.querySelectorAll('meta[property="og:image"],meta[name="twitter:image"]').forEach(function(m){add(m.getAttribute('content'))});
Array.prototype.slice.call(document.images).filter(function(i){var r=i.getBoundingClientRect();return i.naturalWidth>=300&&i.naturalHeight>=300&&r.width>=150}).sort(function(a,b){return a.getBoundingClientRect().top-b.getBoundingClientRect().top}).slice(0,10).forEach(function(i){add(i.currentSrc||i.src)});
var o=p&&p.offers;if(Array.isArray(o))o=o[0];
var name=(p&&p.name)||q('meta[property="og:title"]')||q('h1')||document.title;
var desc=(p&&typeof p.description==='string'&&p.description)||q('meta[property="og:description"]')||q('meta[name="description"]')||'';
var d={url:location.href,name:String(name).slice(0,480),description:String(desc).slice(0,5000),images:imgs.slice(0,12),price:String((o&&(o.price||o.lowPrice))||q('meta[property="product:price:amount"]')||'').slice(0,30),currency:String((o&&o.priceCurrency)||q('meta[property="product:price:currency"]')||'').slice(0,8),text:String(document.body?document.body.innerText:'').replace(/[ \t]+/g,' ').replace(/\n\s*\n+/g,'\n').slice(0,20000)};
if(!d.images.length&&!d.name){alert('Shopio : ouvre la page d’un produit, puis touche ce bouton.');return}
var to=S+'#import='+encodeURIComponent(JSON.stringify(d));var w=window.open(to,'_blank');if(!w)location.href=to;
})();
JS;

    /** Adresse du favori (javascript:…), qui ouvre l'assistant $target. */
    public static function href(string $target): string
    {
        $js = str_replace('__TARGET__', json_encode($target, JSON_UNESCAPED_SLASHES), self::JS);
        return 'javascript:' . rawurlencode(preg_replace('/\n/', '', $js));
    }
}
