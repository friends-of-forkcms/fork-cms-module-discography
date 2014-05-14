{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
    <h2>{$lblDiscography|ucfirst}: {$lblEdit}</h2>
</div>

{form:edit}
    <label for="title">{$lblTitle|ucfirst}</label>
    {$txtTitle} {$txtTitleError}

    <div class="fullwidthOptions">
        {option:showDiscographyDeleteCategory}
            <a href="{$var|geturl:'delete_category'}&amp;id={$item.id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
                <span>{$lblDelete|ucfirst}</span>
            </a>
            <div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
                <p>
                    {$msgConfirmDeleteCategory|sprintf:{$item.title}}
                </p>
            </div>
        {/option:showDiscographyDeleteCategory}
        <div class="buttonHolderRight">
            <input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblSave|ucfirst}" />
        </div>
    </div>
{/form:edit}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}
