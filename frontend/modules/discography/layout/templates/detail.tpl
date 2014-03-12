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
	<div id="discographyIndex">
		<article class="mod">
			<div class="inner">
				<header class="hd">
					<h3>{$album.title}</h3>
				</header>
				<div class="bd content">
					<div class="coverImage">
						{option:album.image}<img src="{$FRONTEND_FILES_URL}/discography/images/300x300/{$album.image}" alt="{$album.title}">{/option:album.image}
						{option:!album.image}<img src="{$FRONTEND_FILES_URL}/discography/images/300x300/placeholder.png" alt="{$album.title}">{/option:!album.image}
					</div>
					<div class="tracks">
						<h4>Tracklisting:</h4>
						<ol>
							{iteration:album.tracks}
								<li>{$album.tracks.title} <span>({$album.tracks.duration|substring:0:5})</span></li>
							{/iteration:album.tracks}
						</ol>
					</div>
				</div>
			</div>
		</article>
	</div>
	{include:core/layout/templates/pagination.tpl}
{/option:album}