{{--
    Notifications de campagnes non lues (vendeur ou équipe Shopio). Un clic ouvre la page concernée
    et marque la notification comme lue. Variables : $news (notifications), $readAllUrl
--}}
@if($news->isNotEmpty())
<section class="cp-card cp-news" aria-labelledby="titre-nouveautes">
    <div class="cp-news-hd">
        <h2 id="titre-nouveautes">Nouveautés <span class="cp-news-n">{{ $news->count() }}</span></h2>
        <form method="POST" action="{{ $readAllUrl }}">@csrf <button type="submit" class="cp-link cp-news-all">Tout marquer comme lu</button></form>
    </div>
    <ul class="cp-news-list">
        @foreach($news as $n)
        <li>
            <a href="{{ route('notifications.read', $n->id) }}" class="cp-news-item">
                <span class="cp-news-dot" aria-hidden="true"></span>
                <span style="min-width:0;flex:1">
                    <span class="cp-news-title">{{ $n->data['title'] ?? 'Campagne' }}</span>
                    <span class="cp-news-body">{{ $n->data['body'] ?? '' }}</span>
                    <span class="cp-news-time">{{ $n->created_at->diffForHumans() }}</span>
                </span>
                @if(!empty($n->data['action']))<span class="cp-news-act">{{ $n->data['action'] }}</span>@endif
            </a>
        </li>
        @endforeach
    </ul>
</section>
@endif
