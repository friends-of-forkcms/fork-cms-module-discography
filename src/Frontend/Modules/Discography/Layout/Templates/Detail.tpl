{option:!album}
	<div id="discographyIndex">
		<section class="mod">
			<div class="inner">
				<div class="bd content">
					<p>{$msgNoalbum}</p>
				</div>
			</div>
		</section>
	</div>
{/option:!album}

{option:album}
		<section class="discography-detail">
			<header class="hd">
				<h3>{$album.title} ({$album.release_date|date:'Y'})</h3>
			</header>

			<div class="album-thumbnail">
				{option:album.image}<img src="{$FRONTEND_FILES_URL}/discography/images/300x300/{$album.image}" alt="{$album.title}">{/option:album.image}
				{option:!album.image}<img src="{$FRONTEND_FILES_URL}/discography/images/300x300/placeholder.png" alt="{$album.title}">{/option:!album.image}
			</div>

			<div class="album-tracks">
				<h4>Tracklisting:</h4>
				<ol>
					{iteration:album.tracks}
						<li>{$album.tracks.title} <span>({$album.tracks.duration|substring:0:5})</span></li>
					{/iteration:album.tracks}
				</ol>
			</div>

            <div class="album-info">
                <h4>Album info:</h4>
                <ol>
                    <li>Release date: {$album.release_date}</li>
                </ol>
            </div>

		</section>

		<p>
			<a href="{$discographyUrl}" class="button">{$lblPrevious}</a>
		</p>

	{include:core/layout/templates/pagination.tpl}
{/option:album}