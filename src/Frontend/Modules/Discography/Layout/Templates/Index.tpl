{option:!items}
    <div id="discographyIndex">
        <section class="mod">
            <div class="inner">
                <div class="bd content">
                    <p>{$msgDiscographyNoItems}</p>
                </div>
            </div>
        </section>
    </div>
{/option:!items}

{option:items}
    <div id="discography-main">
        {iteration:items}
            <section class="discography-category">
                <header class="hd">
                    <h3>{$items.name}</h3>
                </header>

                {iteration:items.albums}
                    <div class="wrap {option:items.albums.first}first{/option:items.albums.first}">
                        <div class="album-thumbnail">
                            <a class="cover" href="{$items.albums.full_url}">
                                {option:items.albums.image}<img src="{$FRONTEND_FILES_URL}/Discography/images/150x150/{$items.albums.image}" alt="{$items.albums.title}">{/option:items.albums.image}
                                {option:!items.albums.image}<img src="{$FRONTEND_FILES_URL}/Discography/images/150x150/placeholder.png" alt="{$items.albums.title}">{/option:!items.albums.image}
                            </a>
                        </div>

                        <div class="album-label">
                            <div class="name"><a href="{$items.albums.full_url}" title="{$items.albums.title}">{$items.albums.title}</a></div>
                            <div class="year">{$items.albums.release_date|date:'Y'}</div>
                        </div>
                    </div>
                {/iteration:items.albums}
            </section>

        {option:!items.last} <hr> {/option:!items.last}
        {/iteration:items}
    </div>

    {include:core/layout/templates/pagination.tpl}
{/option:items}
