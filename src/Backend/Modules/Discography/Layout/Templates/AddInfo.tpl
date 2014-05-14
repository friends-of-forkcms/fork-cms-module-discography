{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
    <h2>{$lblDiscography|ucfirst}: {$lblAdd} "{$title}"</h2>
</div>

<div class="wizard">
    <ul>
        <li class="beforeSelected"><a href="{$var|geturl:'add'}&amp;id={$id}"><b><span>1.</span> {$lblWizardTitle|ucfirst}</b></a></li>
        <li class="selected"><a href="{$var|geturl:'add_info'}&amp;id={$id}"><b><span>2.</span> {$lblWizardInfo|ucfirst}</b></a></li>
    </ul>
</div>

{form:add}
    <div class="tabs">
        <ul>
            <li><a href="#tabContent">{$lblTracks|ucfirst}</a></li>
            <li><a href="#tabSEO">{$lblSEO|ucfirst}</a></li>
        </ul>

        <div id="tabContent">
            <table border="0" cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td id="leftColumn">

                        {* Main content *}
                        <script type="text/javascript">
                            var tracks = {$tracks};
                        </script>

                        <div class="dataGridHolder">
                            <table id="tracksWrapper" class="sequenceByDragAndDrop dataGrid">
                                <thead>
                                    <tr>
                                        <th style="width: 28px"><span>&nbsp;</span></th>
                                        <th style="width: 70px"><span>{$lblNumberAbbr|ucfirst}</span></th>
                                        <th><span>{$lblTitle|ucfirst}</span></th>
                                        <th><span>{$lblDuration|ucfirst}</span></th>
                                        <th><span>&nbsp;</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="dummyTrack" class="track">
                                        <td class="dragAndDropHandle"><span>{$lblMove}</span></td>
                                        <td class="tracknr"></td>
                                        <td class="title">{$txtTrack}</td>
                                        <td class="duration">{$txtDuration}</td>
                                        <td class="buttonAction">
                                            <a class="button icon iconDelete iconOnly deleteTrack" title="{$lblDelete|ucfirst}" href="#"><span>{$lblDelete|ucfirst}</span></a>
                                        </td>
                                    </tr>
                                    <tr class="noItemsHolder">
                                        <td colspan="5">
                                            {$msgNoTracks}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5">
                                            <div class="tableOptionsHolder">
                                                <div class="tableOptions">
                                                    <div class="buttonHolderRight">
                                                        <a href="#" class="button icon iconAdd addTrack"><span>{$lblAddTrack|ucfirst}</span></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            {option:noTracks}
                                <p class="formError">
                                    {$errNoTracks}
                                </p>
                            {/option:noTracks}
                            {$hidDummyTracks}
                        </div>

                    </td>

                    <td id="sidebar">
                        <div id="publishOptions" class="box">
                            <div class="heading">
                                <h3>{$lblInfo|ucfirst}</h3>
                            </div>

                            <div class="options">
                                <ul class="inputList">
                                    {iteration:hidden}
                                        <li>
                                            {$hidden.rbtHidden}
                                            <label for="{$hidden.id}">{$hidden.label}</label>
                                        </li>
                                    {/iteration:hidden}
                                </ul>
                            </div>

                            <div class="options">
                                <p class="p0"><label for="publishOnDate">{$lblReleaseDate|ucfirst}</label></p>
                                <div class="oneLiner">
                                    <p>
                                        {$txtReleaseDate} {$txtReleaseDateError}
                                    </p>
                                </div>
                            </div>

                            <div class="options">
                                <p class="p0"><label for="categoryId">{$lblCategory|ucfirst}</label></p>
                                <div class="oneLiner">
                                    <p>
                                        {$ddmCategoryId} {$ddmCategoryIdError}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {* Image *}
                        <div class="box">
                            <div class="heading">
                                <h3>{$lblImage|ucfirst}</h3>
                            </div>
                            <div class="options clearfix">
                                {option:item.image}
                                    <p class="imageHolder">
                                        <img src="{$FRONTEND_FILES_URL}/discography/images/128x128/{$item.image}" width="128" height="128" alt="{$lblImage|ucfirst}" />
                                        <label for="deleteImage">{$chkDeleteImage} {$lblDelete|ucfirst}</label>
                                        {$chkDeleteImageError}
                                    </p>
                                {/option:item.image}
                                <p>
                                    <label for="image">{$lblImage|ucfirst}</label>
                                    {$fileImage} {$fileImageError}
                                </p>
                            </div>
                        </div>

                    </td>
                </tr>
            </table>
        </div>

        <div id="tabSEO">
            {include:{$BACKEND_CORE_PATH}/layout/templates/seo.tpl}
        </div>
    </div>

    <div class="fullwidthOptions">
        <a href="{$var|geturl:'delete'}&amp;id={$id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
            <span>{$lblDelete|ucfirst}</span>
        </a>
        <div class="buttonHolderRight">
            <input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblSave|ucfirst}" />
        </div>
    </div>

    <div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
        <p>
            {$msgConfirmDelete|sprintf:{$title}}
        </p>
    </div>


{/form:add}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}
