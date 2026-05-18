<?php 
 $__env->startSection('title', 'Accueil — Marketplace'); 
 $bodyClass = 'is-dashboard'; 
 $__env->startPush('styles'); 
 $__env->stopPush(); 
 $__env->startSection('content'); 

    $user      = auth()->user();
    $parts     = explode(' ', $user->name);
    $initials  = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $firstName = $parts[0];

    // Drapeau pays
    $countryFlag = '';
    if ($user->country) {
        $c = strtoupper($user->country);
        $countryFlag = mb_convert_encoding(
            '&#'.(127397+ord($c[0])).';&#'.(127397+ord($c[1])).';',
            'UTF-8', 'HTML-ENTITIES'
        );
    }

    $countryNames = [
        // Afrique de l'Ouest
        'BJ'=>'Bénin','BF'=>'Burkina Faso','CV'=>'Cap-Vert','CI'=>"Côte d'Ivoire",
        'GM'=>'Gambie','GH'=>'Ghana','GN'=>'Guinée','GW'=>'Guinée-Bissau','LR'=>'Libéria',
        'ML'=>'Mali','MR'=>'Mauritanie','NE'=>'Niger','NG'=>'Nigéria','SN'=>'Sénégal',
        'SL'=>'Sierra Leone','TG'=>'Togo',
        // Afrique Centrale
        'CM'=>'Cameroun','CF'=>'Centrafrique','TD'=>'Tchad','CG'=>'Congo','CD'=>'RD Congo',
        'GQ'=>'Guinée Équatoriale','GA'=>'Gabon','ST'=>'São Tomé','BI'=>'Burundi','RW'=>'Rwanda',
        // Afrique de l'Est
        'DJ'=>'Djibouti','ER'=>'Érythrée','ET'=>'Éthiopie','KE'=>'Kenya','KM'=>'Comores',
        'MG'=>'Madagascar','MW'=>'Malawi','MU'=>'Maurice','MZ'=>'Mozambique','SC'=>'Seychelles',
        'SO'=>'Somalie','SS'=>'Soudan du Sud','SD'=>'Soudan','TZ'=>'Tanzanie','UG'=>'Ouganda',
        'ZM'=>'Zambie','ZW'=>'Zimbabwe',
        // Afrique du Nord
        'DZ'=>'Algérie','EG'=>'Égypte','LY'=>'Libye','MA'=>'Maroc','TN'=>'Tunisie',
        // Afrique Australe
        'AO'=>'Angola','BW'=>'Botswana','LS'=>'Lesotho','NA'=>'Namibie','ZA'=>'Afrique du Sud','SZ'=>'Eswatini',
        // Europe
        'AL'=>'Albanie','DE'=>'Allemagne','AT'=>'Autriche','BE'=>'Belgique','BA'=>'Bosnie',
        'BG'=>'Bulgarie','HR'=>'Croatie','CY'=>'Chypre','DK'=>'Danemark','ES'=>'Espagne',
        'EE'=>'Estonie','FI'=>'Finlande','FR'=>'France','GR'=>'Grèce','HU'=>'Hongrie',
        'IE'=>'Irlande','IS'=>'Islande','IT'=>'Italie','LV'=>'Lettonie','LT'=>'Lituanie',
        'LU'=>'Luxembourg','MT'=>'Malte','MD'=>'Moldavie','MC'=>'Monaco','ME'=>'Monténégro',
        'NO'=>'Norvège','NL'=>'Pays-Bas','PL'=>'Pologne','PT'=>'Portugal','CZ'=>'Rép. Tchèque',
        'RO'=>'Roumanie','GB'=>'Royaume-Uni','RU'=>'Russie','RS'=>'Serbie','SK'=>'Slovaquie',
        'SI'=>'Slovénie','SE'=>'Suède','CH'=>'Suisse','UA'=>'Ukraine',
        // Amériques
        'AR'=>'Argentine','BR'=>'Brésil','CA'=>'Canada','CL'=>'Chili','CO'=>'Colombie',
        'CU'=>'Cuba','DO'=>'Rép. Dominicaine','EC'=>'Équateur','US'=>'États-Unis',
        'GT'=>'Guatemala','HT'=>'Haïti','MX'=>'Mexique','PA'=>'Panama','PE'=>'Pérou',
        'UY'=>'Uruguay','VE'=>'Venezuela',
        // Asie
        'SA'=>'Arabie Saoudite','AM'=>'Arménie','AZ'=>'Azerbaïdjan','BD'=>'Bangladesh',
        'CN'=>'Chine','KR'=>'Corée du Sud','AE'=>'Émirats Arabes','IN'=>'Inde',
        'ID'=>'Indonésie','IR'=>'Iran','IQ'=>'Irak','IL'=>'Israël','JP'=>'Japon',
        'JO'=>'Jordanie','KW'=>'Koweït','LB'=>'Liban','MY'=>'Malaisie','NP'=>'Népal',
        'OM'=>'Oman','PK'=>'Pakistan','PH'=>'Philippines','QA'=>'Qatar','SG'=>'Singapour',
        'LK'=>'Sri Lanka','TH'=>'Thaïlande','TR'=>'Turquie','VN'=>'Viêt Nam',
        // Océanie
        'AU'=>'Australie','NZ'=>'Nouvelle-Zélande',
    ];
    $countryName = $countryNames[$user->country ?? ''] ?? $user->country ?? '';


    $myMessages ??= collect();
    $myUnread   ??= 0;

    $statusMap = [
        'livrée'       => ['pill-livree',    '✓ Livrée'],
        'pending'      => ['pill-pending',   '⏳ En attente'],
        'en attente'   => ['pill-pending',   '⏳ En attente'],
        'en_attente'   => ['pill-pending',   '⏳ En attente'],
        'confirmée'    => ['pill-livraison', '✓ Confirmée'],
        'en_livraison' => ['pill-livraison', '🚴 En livraison'],
        'annulée'      => ['pill-cancelled', '✕ Annulée'],
        'cancelled'    => ['pill-cancelled', '✕ Annulée'],
    ];

    $typeIco = [
        'Alimentation' => ['🥩', 'bg-food'],    'Restaurant'  => ['🍽️', 'bg-food'],
        'Épicerie'     => ['🛒', 'bg-food'],    'Boulangerie' => ['🥖', 'bg-food'],
        'Vêtements'    => ['👗', 'bg-fashion'], 'Bijouterie'  => ['💎', 'bg-fashion'],
        'Électronique' => ['📱', 'bg-tech'],    'Informatique'=> ['💻', 'bg-tech'],
        'Téléphonie'   => ['📞', 'bg-tech'],    'Beauté & Cosmétiques' => ['💄', 'bg-beauty'],
        'Pharmacie'    => ['💊', 'bg-beauty'],  'Parfumerie'  => ['🌸', 'bg-beauty'],
    ];

    // Catégories à afficher seulement si des boutiques de ce type existent
    $allTypes = ['Alimentation','Restaurant','Épicerie','Boulangerie','Vêtements','Bijouterie',
                 'Électronique','Informatique','Téléphonie','Beauté & Cosmétiques','Pharmacie','Parfumerie'];
    $activeType = request('type', '');

 if($myUnread > 0): 
 echo e($myUnread); 
 echo e($myUnread > 1 ? 's' : ''); 
 endif; 
 $__empty_1 = true; $__currentLoopData = $myMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $convKey => $msgs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; 

            $firstMsg = $msgs->first();
            $lastMsg  = $msgs->first(); /* déjà orderByDesc donc premier = plus récent */
            $product  = $firstMsg->product;
            $shop     = $product?->shop;
            $vendeur  = ($firstMsg->sender_id === $user->id) ? $firstMsg->receiver : $firstMsg->sender;
            $unreadCnt = $msgs->filter(fn($m) => is_null($m->read_at) && $m->receiver_id === $user->id)->count();
            $vName  = $shop?->name ?? ($vendeur?->name ?? 'Vendeur');
            $vParts = explode(' ', $vName);
            $vInit  = strtoupper(substr($vParts[0],0,1)) . strtoupper(substr($vParts[1] ?? 'X',0,1));
            $convData = json_encode([
                'key'      => $convKey,
                'shopName' => $vName,
                'shopImg'  => $shop?->image ? asset('storage/'.$shop->image) : null,
                'shopInit' => $vInit,
                'prodName' => $product?->name ?? '',
                'prodImg'  => $product?->image ? asset('storage/'.$product->image) : null,
                'prodPrice'=> $product ? number_format($product->price,0,',',' ').' GNF' : '',
                'prodUrl'  => $product ? route('client.products.show', $product) : '#',
                'productId'=> $product?->id,
                'msgs'     => $msgs->map(fn($m) => [
                    'id'   => $m->id,
                    'body' => $m->body,
                    'mine' => $m->sender_id === $user->id,
                    'av'   => $m->sender_id === $user->id ? $initials : $vInit,
                    'time' => $m->created_at->format('d/m H:i'),
                    'read' => !is_null($m->read_at),
                ])->values(),
            ]);
        
 echo e($unreadCnt > 0 ? 'has-unread' : ''); 
 echo e($convData); 
 echo e($convKey); 
 if($shop?->image): 
 echo e(\App\Services\ImageOptimizer::url($shop->image, 'thumb')); 
 echo e($vName); 
 else: 
 echo e($vInit); 
 endif; 
 if($unreadCnt > 0): 
 endif; 
 echo e($vName); 
 if($product): 
 echo e(Str::limit($product->name, 28)); 
 endif; 
 echo e(Str::limit($lastMsg->body, 42)); 
 echo e($lastMsg->created_at->diffForHumans(null, true)); 
 if($unreadCnt > 0): 
 echo e($unreadCnt); 
 endif; 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): 
 endif; 
 echo csrf_field(); 
 echo e(route('client.dashboard')); 
 echo e(asset('images/Shopio2.jpeg')); 
 echo e(route('client.dashboard')); 
 echo e(route('client.messages.hub')); 
 echo e($myUnread > 0 ? 'show' : ''); 
 echo e($myUnread > 0 ? $myUnread : ''); 
 echo e(route('client.orders.index')); 
 echo e($initials); 
 if($countryFlag): 
 echo e($countryFlag); 
 endif; 
 echo e($initials); 
 echo e($user->name); 
 echo e($user->email); 
 if($countryFlag): 
 echo e($countryFlag); 
 echo e($countryName); 
 endif; 
 echo e(route('client.orders.index')); 
 echo e(route('logout')); 
 echo csrf_field(); 
 if(isset($categories) && $categories->isNotEmpty()): 
 echo e($shopCount ?? $shops->total()); 

            $sbEmojis = [
                'alimentation'=>'🍽️','restaurant'=>'🍽️','épicerie'=>'🛒','epicerie'=>'🛒',
                'boulangerie'=>'🥖','pâtisserie'=>'🎂','patisserie'=>'🎂','vêtements'=>'👗',
                'vetements'=>'👗','mode'=>'👗','bijouterie'=>'💎','bijoux'=>'💎',
                'électronique'=>'📱','electronique'=>'📱','informatique'=>'💻',
                'téléphonie'=>'📞','telephonie'=>'📞','beauté & cosmétiques'=>'💄',
                'beaute & cosmetiques'=>'💄','beauté'=>'💄','beaute'=>'💄',
                'cosmétiques'=>'💄','cosmetiques'=>'💄','pharmacie'=>'💊',
                'parfumerie'=>'🌸','auto & moto'=>'🚗','automobile'=>'🚗','sport'=>'⚽',
                'maison'=>'🏠','décoration'=>'🏠','decoration'=>'🏠','librairie'=>'📚',
                'musique'=>'🎵','jardin'=>'🌿','agriculture'=>'🌾','santé'=>'🏥',
                'sante'=>'🏥','construction'=>'🏗️','quincaillerie'=>'🔧','supermarché'=>'🛒',
                'supermarche'=>'🛒','chaussures'=>'👟','accessoires'=>'👜','sacs'=>'👜',
            ];
            $getSbEmoji = function(string $t) use ($sbEmojis): string {
                $k = mb_strtolower(trim($t));
                if (isset($sbEmojis[$k])) return $sbEmojis[$k];
                foreach ($sbEmojis as $key => $ico) { if (str_contains($k, $key)) return $ico; }
                return '🏪';
            };
        
 $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 
 echo e($cat->type); 
 echo e($getSbEmoji($cat->type)); 
 echo e($cat->type); 
 echo e($cat->shop_count); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 endif; 
 if($countryFlag && $countryName): 
 echo e($countryFlag); 
 echo e($countryName); 
 endif; 
 if(isset($topShops) && $topShops->isNotEmpty()): 
 $__currentLoopData = $topShops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $ts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 
 echo e(route('client.shops.show', $ts)); 
 echo e($ts->id); 
 echo e($i + 1); 
 if($ts->image): 
 echo e(\App\Services\ImageOptimizer::url($ts->image, 'thumb')); 
 echo e($ts->name); 
 else: 
 endif; 
 echo e($ts->name); 
 echo e($ts->avg_rating ? number_format($ts->avg_rating, 1) : '—'); 
 echo e($ts->reviews_count ?? 0); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 endif; 
 if($countryName): 
 echo e($countryName); 
 else: 
 echo e(number_format($shopCount ?? 0)); 
 echo e(number_format($productCount ?? 0)); 
 echo e(number_format($deliveredCount ?? 0)); 
 echo e(number_format($clientCount ?? 0)); 
 $__currentLoopData = ['success','danger']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 
 if(session($t)): 
 echo e($t); 
 echo e($t === 'success' ? '✓' : '✕'); 
 echo e(session($t)); 
 endif; 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 if(isset($recentOrders) && $recentOrders->isNotEmpty()): 
 echo e(route('client.orders.index')); 
 echo e($recentOrders->count()); 
 $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 

            $st   = $statusMap[$order->status] ?? ['pill-pending', ucfirst($order->status)];
            $oIco = match($order->status) {
                'livrée'                     => ['🎉', 'background:#d1fae5'],
                'en_livraison','en livraison' => ['🚴', 'background:#dbeafe'],
                'annulée','cancelled'          => ['✕',  'background:#fee2e2'],
                default                       => ['📦', 'background:#fef3c7'],
            };
        
 echo e(route('client.orders.index')); 
 echo e($order->id); 
 echo e($order->status); 
 echo e($order->id); 
 echo e($oIco[1]); 
 echo e($oIco[0]); 
 echo e($order->id); 
 echo e($order->shop?->name ?? 'Boutique'); 
 echo e($st[0]); 
 echo e($order->id); 
 echo e($st[1]); 
 echo e(number_format($order->total, 0, ',', ' ')); 
 echo e($order->shop?->currency ?? 'GNF'); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 endif; 
 if(isset($categories) && $categories->isNotEmpty()): 
 $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 
 echo e($cat->type); 
 echo e($getSbEmoji($cat->type)); 
 echo e($cat->type); 
 echo e($cat->shop_count); 
 echo e($cat->shop_count > 1 ? 's' : ''); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 endif; 
 if(isset($topShops) && $topShops->isNotEmpty()): 
 $__currentLoopData = $topShops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 
 [$tsIco, $tsBg] = $typeIco[$ts->type ?? ''] ?? ['🛍️', 'bg-default']; 
 echo e($ts->id); 
 if($ts->image): 
 echo e(\App\Services\ImageOptimizer::url($ts->image, 'medium')); 
 echo e($ts->name); 
 else: 
 echo e($tsBg); 
 echo e($tsIco); 
 endif; 
 echo e($ts->name); 
 echo e($ts->avg_rating ? number_format($ts->avg_rating, 1) : '—'); 
 echo e($ts->reviews_count ?? 0); 
 echo e($ts->sales_count ?? 0); 
 echo e(route('client.shops.show', $ts)); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 endif; 
 echo e($shops->total()); 

                /* ── Emojis prédéfinis (extensible) ── */
                $catEmojis = [
                    'alimentation'          => '🍽️',
                    'restaurant'            => '🍽️',
                    'épicerie'              => '🛒',
                    'epicerie'              => '🛒',
                    'boulangerie'           => '🥖',
                    'pâtisserie'            => '🎂',
                    'patisserie'            => '🎂',
                    'vêtements'             => '👗',
                    'vetements'             => '👗',
                    'mode'                  => '👗',
                    'bijouterie'            => '💎',
                    'bijoux'                => '💎',
                    'électronique'          => '📱',
                    'electronique'          => '📱',
                    'informatique'          => '💻',
                    'téléphonie'            => '📞',
                    'telephonie'            => '📞',
                    'beauté & cosmétiques'  => '💄',
                    'beaute & cosmetiques'  => '💄',
                    'beauté'                => '💄',
                    'beaute'                => '💄',
                    'cosmétiques'           => '💄',
                    'cosmetiques'           => '💄',
                    'pharmacie'             => '💊',
                    'parfumerie'            => '🌸',
                    'auto & moto'           => '🚗',
                    'auto'                  => '🚗',
                    'moto'                  => '🏍️',
                    'automobile'            => '🚗',
                    'sport'                 => '⚽',
                    'sport & loisirs'       => '⚽',
                    'jouets'                => '🧸',
                    'enfants'               => '🧸',
                    'maison'                => '🏠',
                    'décoration'            => '🏠',
                    'decoration'            => '🏠',
                    'mobilier'              => '🛋️',
                    'librairie'             => '📚',
                    'livres'                => '📚',
                    'musique'               => '🎵',
                    'téléphone'             => '📱',
                    'telephone'             => '📱',
                    'high-tech'             => '🖥️',
                    'high tech'             => '🖥️',
                    'jardin'                => '🌿',
                    'agriculture'           => '🌾',
                    'animalerie'            => '🐾',
                    'voyage'                => '✈️',
                    'artisanat'             => '🎨',
                    'art'                   => '🎨',
                    'santé'                 => '🏥',
                    'sante'                 => '🏥',
                    'médical'               => '🏥',
                    'medical'               => '🏥',
                    'construction'          => '🏗️',
                    'quincaillerie'         => '🔧',
                    'outillage'             => '🔧',
                    'fournitures'           => '✏️',
                    'bureau'                => '✏️',
                    'supermarché'           => '🛒',
                    'supermarche'           => '🛒',
                    'épices'                => '🌶️',
                    'épice'                 => '🌶️',
                    'boissons'              => '🥤',
                    'chaussures'            => '👟',
                    'accessoires'           => '👜',
                    'sacs'                  => '👜',
                ];

                /* Récupère TOUS les types distincts des boutiques approuvées du pays */
                $existingTypes = \App\Models\Shop::where('is_approved', true)
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->when(auth()->user()?->country, fn($q, $c) => $q->where('country', $c))
                    ->distinct()
                    ->orderBy('type')
                    ->pluck('type')
                    ->toArray();

                /* Fonction : trouver l'emoji pour un type (clé normalisée) */
                $getEmoji = function(string $type) use ($catEmojis): string {
                    $key = mb_strtolower(trim($type));
                    // Correspondance exacte d'abord
                    if (isset($catEmojis[$key])) return $catEmojis[$key];
                    // Correspondance partielle (ex: "Auto & Moto occasion" → 'auto')
                    foreach ($catEmojis as $k => $e) {
                        if (str_contains($key, $k)) return $e;
                    }
                    return '🏪'; // fallback
                };
            
 echo e($activeType === '' ? 'active' : ''); 
 echo e($shops->total()); 
 $__currentLoopData = $existingTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 
 echo e($activeType === $t ? 'active' : ''); 
 echo e($t); 
 echo e($getEmoji($t)); 
 echo e($t); 
 echo e($t); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 $__empty_1 = true; $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; 

                [$ico, $bgClass] = $typeIco[$shop->type ?? ''] ?? ['🛍️', 'bg-default'];
                $isNew = $shop->created_at->diffInDays(now()) <= 7;
            
 echo e(route('client.shops.show', $shop)); 
 echo e(strtolower($shop->name)); 
 echo e(strtolower($shop->type ?? '')); 
 if($shop->image): 
 echo e(\App\Services\ImageOptimizer::url($shop->image, 'thumb')); 
 echo e(\App\Services\ImageOptimizer::url($shop->image, 'thumb')); 
 echo e(\App\Services\ImageOptimizer::url($shop->image, 'medium')); 
 echo e($shop->name); 
 else: 
 echo e($bgClass); 
 echo e($ico); 
 endif; 
 if($isNew): 
 else: 
 endif; 
 if($shop->type): 
 echo e($shop->type); 
 endif; 
 echo e($shop->name); 
 if($shop->description): 
 echo e($shop->description); 
 endif; 
 if($shop->address ?? false): 
 echo e(Str::limit($shop->address, 18)); 
 endif; 
 if(($shop->products_count ?? 0) > 0): 
 echo e($shop->products_count); 
 echo e($shop->products_count > 1 ? 's' : ''); 
 endif; 
 echo e($shop->avg_rating ? number_format($shop->avg_rating, 1) : '—'); 
 echo e($shop->reviews_count ?? 0); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): 
 endif; 
 echo e($shops->links()); 
 echo e($initials); 
 if($countryFlag): 
 echo e($countryFlag); 
 endif; 
 echo e($user->name); 
 echo e($user->email); 
 if($countryName): 
 echo e($countryFlag); 
 echo e($countryName); 
 endif; 
 echo e(route('profile.update')); 
 echo csrf_field(); 
 echo method_field('PATCH'); 
 if($errors->any() && !$errors->updatePassword->any() && !$errors->userDeletion->any()): 
 $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 
 echo e($e); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 endif; 
 if(session('status') === 'profile-updated'): 
 endif; 

                        $iStyle = "width:100%;padding:10px 14px 10px 40px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;outline:none;box-sizing:border-box";
                        $lStyle = "display:block;font-size:11px;font-weight:700;color:var(--text-2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.6px";
                        
 echo e($lStyle); 
 echo e(old('name', $user->name)); 
 echo e($iStyle); 
 echo e($lStyle); 
 echo e(old('email', $user->email)); 
 echo e($iStyle); 
 echo e($lStyle); 
 echo e(old('phone', $user->phone)); 
 echo e($iStyle); 
 echo e($lStyle); 
 echo e(old('address', $user->address)); 
 echo e($iStyle); 
 echo e($lStyle); 
 echo e($countryFlag ?: '🌍'); 

                                    $allCountries = [
                                        'Afrique de l\'Ouest' => [
                                            'BJ'=>'🇧🇯 Bénin','BF'=>'🇧🇫 Burkina Faso','CV'=>'🇨🇻 Cap-Vert',
                                            'CI'=>"🇨🇮 Côte d'Ivoire",'GM'=>'🇬🇲 Gambie','GH'=>'🇬🇭 Ghana',
                                            'GN'=>'🇬🇳 Guinée','GW'=>'🇬🇼 Guinée-Bissau','LR'=>'🇱🇷 Libéria',
                                            'ML'=>'🇲🇱 Mali','MR'=>'🇲🇷 Mauritanie','NE'=>'🇳🇪 Niger',
                                            'NG'=>'🇳🇬 Nigéria','SN'=>'🇸🇳 Sénégal','SL'=>'🇸🇱 Sierra Leone',
                                            'TG'=>'🇹🇬 Togo',
                                        ],
                                        'Afrique Centrale' => [
                                            'CM'=>'🇨🇲 Cameroun','CF'=>'🇨🇫 Centrafrique','TD'=>'🇹🇩 Tchad',
                                            'CG'=>'🇨🇬 Congo','CD'=>'🇨🇩 RD Congo','GQ'=>'🇬🇶 Guinée Équatoriale',
                                            'GA'=>'🇬🇦 Gabon','ST'=>'🇸🇹 São Tomé','BI'=>'🇧🇮 Burundi',
                                            'RW'=>'🇷🇼 Rwanda',
                                        ],
                                        'Afrique de l\'Est' => [
                                            'DJ'=>'🇩🇯 Djibouti','ER'=>'🇪🇷 Érythrée','ET'=>'🇪🇹 Éthiopie',
                                            'KE'=>'🇰🇪 Kenya','KM'=>'🇰🇲 Comores','MG'=>'🇲🇬 Madagascar',
                                            'MW'=>'🇲🇼 Malawi','MU'=>'🇲🇺 Maurice','MZ'=>'🇲🇿 Mozambique',
                                            'SC'=>'🇸🇨 Seychelles','SO'=>'🇸🇴 Somalie','SS'=>'🇸🇸 Soudan du Sud',
                                            'SD'=>'🇸🇩 Soudan','TZ'=>'🇹🇿 Tanzanie','UG'=>'🇺🇬 Ouganda',
                                            'ZM'=>'🇿🇲 Zambie','ZW'=>'🇿🇼 Zimbabwe',
                                        ],
                                        'Afrique du Nord' => [
                                            'DZ'=>'🇩🇿 Algérie','EG'=>'🇪🇬 Égypte','LY'=>'🇱🇾 Libye',
                                            'MA'=>'🇲🇦 Maroc','SD'=>'🇸🇩 Soudan','TN'=>'🇹🇳 Tunisie',
                                        ],
                                        'Afrique Australe' => [
                                            'AO'=>'🇦🇴 Angola','BW'=>'🇧🇼 Botswana','LS'=>'🇱🇸 Lesotho',
                                            'NA'=>'🇳🇦 Namibie','ZA'=>'🇿🇦 Afrique du Sud','SZ'=>'🇸🇿 Eswatini',
                                        ],
                                        'Europe' => [
                                            'AL'=>'🇦🇱 Albanie','DE'=>'🇩🇪 Allemagne','AT'=>'🇦🇹 Autriche',
                                            'BE'=>'🇧🇪 Belgique','BA'=>'🇧🇦 Bosnie','BG'=>'🇧🇬 Bulgarie',
                                            'HR'=>'🇭🇷 Croatie','CY'=>'🇨🇾 Chypre','DK'=>'🇩🇰 Danemark',
                                            'ES'=>'🇪🇸 Espagne','EE'=>'🇪🇪 Estonie','FI'=>'🇫🇮 Finlande',
                                            'FR'=>'🇫🇷 France','GR'=>'🇬🇷 Grèce','HU'=>'🇭🇺 Hongrie',
                                            'IE'=>'🇮🇪 Irlande','IS'=>'🇮🇸 Islande','IT'=>'🇮🇹 Italie',
                                            'XK'=>'🇽🇰 Kosovo','LV'=>'🇱🇻 Lettonie','LI'=>'🇱🇮 Liechtenstein',
                                            'LT'=>'🇱🇹 Lituanie','LU'=>'🇱🇺 Luxembourg','MK'=>'🇲🇰 Macédoine',
                                            'MT'=>'🇲🇹 Malte','MD'=>'🇲🇩 Moldavie','MC'=>'🇲🇨 Monaco',
                                            'ME'=>'🇲🇪 Monténégro','NO'=>'🇳🇴 Norvège','NL'=>'🇳🇱 Pays-Bas',
                                            'PL'=>'🇵🇱 Pologne','PT'=>'🇵🇹 Portugal','CZ'=>'🇨🇿 Rép. Tchèque',
                                            'RO'=>'🇷🇴 Roumanie','GB'=>'🇬🇧 Royaume-Uni','RU'=>'🇷🇺 Russie',
                                            'RS'=>'🇷🇸 Serbie','SK'=>'🇸🇰 Slovaquie','SI'=>'🇸🇮 Slovénie',
                                            'SE'=>'🇸🇪 Suède','CH'=>'🇨🇭 Suisse','UA'=>'🇺🇦 Ukraine',
                                        ],
                                        'Amériques' => [
                                            'AR'=>'🇦🇷 Argentine','BB'=>'🇧🇧 Barbade','BO'=>'🇧🇴 Bolivie',
                                            'BR'=>'🇧🇷 Brésil','CA'=>'🇨🇦 Canada','CL'=>'🇨🇱 Chili',
                                            'CO'=>'🇨🇴 Colombie','CR'=>'🇨🇷 Costa Rica','CU'=>'🇨🇺 Cuba',
                                            'DM'=>'🇩🇲 Dominique','DO'=>'🇩🇴 Rép. Dominicaine','EC'=>'🇪🇨 Équateur',
                                            'SV'=>'🇸🇻 Salvador','US'=>'🇺🇸 États-Unis','GT'=>'🇬🇹 Guatemala',
                                            'GY'=>'🇬🇾 Guyana','HT'=>'🇭🇹 Haïti','HN'=>'🇭🇳 Honduras',
                                            'JM'=>'🇯🇲 Jamaïque','MX'=>'🇲🇽 Mexique','NI'=>'🇳🇮 Nicaragua',
                                            'PA'=>'🇵🇦 Panama','PY'=>'🇵🇾 Paraguay','PE'=>'🇵🇪 Pérou',
                                            'TT'=>'🇹🇹 Trinité-et-Tobago','UY'=>'🇺🇾 Uruguay','VE'=>'🇻🇪 Venezuela',
                                        ],
                                        'Asie' => [
                                            'AF'=>'🇦🇫 Afghanistan','AM'=>'🇦🇲 Arménie','AZ'=>'🇦🇿 Azerbaïdjan',
                                            'BH'=>'🇧🇭 Bahreïn','BD'=>'🇧🇩 Bangladesh','BT'=>'🇧🇹 Bhoutan',
                                            'MM'=>'🇲🇲 Birmanie','BN'=>'🇧🇳 Brunei','KH'=>'🇰🇭 Cambodge',
                                            'CN'=>'🇨🇳 Chine','KP'=>'🇰🇵 Corée du Nord','KR'=>'🇰🇷 Corée du Sud',
                                            'AE'=>'🇦🇪 Émirats Arabes','GE'=>'🇬🇪 Géorgie','IN'=>'🇮🇳 Inde',
                                            'ID'=>'🇮🇩 Indonésie','IR'=>'🇮🇷 Iran','IQ'=>'🇮🇶 Irak',
                                            'IL'=>'🇮🇱 Israël','JP'=>'🇯🇵 Japon','JO'=>'🇯🇴 Jordanie',
                                            'KZ'=>'🇰🇿 Kazakhstan','KW'=>'🇰🇼 Koweït','KG'=>'🇰🇬 Kirghizistan',
                                            'LA'=>'🇱🇦 Laos','LB'=>'🇱🇧 Liban','MY'=>'🇲🇾 Malaisie',
                                            'MV'=>'🇲🇻 Maldives','MN'=>'🇲🇳 Mongolie','NP'=>'🇳🇵 Népal',
                                            'OM'=>'🇴🇲 Oman','UZ'=>'🇺🇿 Ouzbékistan','PK'=>'🇵🇰 Pakistan',
                                            'PS'=>'🇵🇸 Palestine','PH'=>'🇵🇭 Philippines','QA'=>'🇶🇦 Qatar',
                                            'SA'=>'🇸🇦 Arabie Saoudite','SG'=>'🇸🇬 Singapour','LK'=>'🇱🇰 Sri Lanka',
                                            'SY'=>'🇸🇾 Syrie','TJ'=>'🇹🇯 Tadjikistan','TW'=>'🇹🇼 Taïwan',
                                            'TH'=>'🇹🇭 Thaïlande','TL'=>'🇹🇱 Timor-Leste','TM'=>'🇹🇲 Turkménistan',
                                            'TR'=>'🇹🇷 Turquie','VN'=>'🇻🇳 Viêt Nam','YE'=>'🇾🇪 Yémen',
                                        ],
                                        'Océanie' => [
                                            'AU'=>'🇦🇺 Australie','FJ'=>'🇫🇯 Fidji','KI'=>'🇰🇮 Kiribati',
                                            'MH'=>'🇲🇭 Îles Marshall','FM'=>'🇫🇲 Micronésie','NR'=>'🇳🇷 Nauru',
                                            'NZ'=>'🇳🇿 Nouvelle-Zélande','PW'=>'🇵🇼 Palaos','PG'=>'🇵🇬 Papouasie',
                                            'WS'=>'🇼🇸 Samoa','SB'=>'🇸🇧 Salomon','TO'=>'🇹🇴 Tonga',
                                            'TV'=>'🇹🇻 Tuvalu','VU'=>'🇻🇺 Vanuatu',
                                        ],
                                    ];
                                    
 $__currentLoopData = $allCountries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region => $pays): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 
 echo e($region); 
 $__currentLoopData = $pays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 
 echo e($code); 
 echo e($user->country === $code ? 'selected' : ''); 
 echo e($label); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 echo e(route('password.update')); 
 echo csrf_field(); 
 echo method_field('PUT'); 
 if($errors->updatePassword->any()): 
 $__currentLoopData = $errors->updatePassword->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 
 echo e($e); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 endif; 
 if(session('status') === 'password-updated'): 
 endif; 
 echo e($errors->updatePassword->has('current_password') ? '#ef4444' : 'var(--border)'); 
 echo e($errors->updatePassword->has('password') ? '#ef4444' : 'var(--border)'); 
 echo e(route('profile.destroy')); 
 echo csrf_field(); 
 echo method_field('DELETE'); 
 if($errors->userDeletion->any()): 
 $__currentLoopData = $errors->userDeletion->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); 
 echo e($e); 
 endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); 
 endif; 
 echo e($errors->userDeletion->has('password') ? '#ef4444' : '#fca5a5'); 
 $__env->stopSection(); 
 $__env->startPush('scripts'); 
 echo e($initials); 
 if(session('status') === 'profile-updated'): 
 endif; 
 if($errors->updatePassword->any()): 
 endif; 
 if($errors->userDeletion->any()): 
 endif; 
 echo e(route("client.messages.client.poll")); 
 echo e($myUnread ?? 0); 
 $__env->stopPush(); 
 echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); 
 /**PATH C:\Users\keita\livraison\resources\views/dashboards/client.blade.php ENDPATH**/ 
?>
