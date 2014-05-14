{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
    <h2>{$lblDiscography|ucfirst}: {$lblCategories}</h2>

    {option:showDiscographyAddCategory}
        <div class="buttonHolderRight">
            <a href="{$var|geturl:'add_category'}" class="button icon iconAdd"><span>{$lblAddCategory|ucfirst}</span></a>
        </div>
    {/option:showDiscographyAddCategory}
</div>

{option:dataGrid}
    <div class="dataGridHolder">
        {$dataGrid}
    </div>
{/option:dataGrid}
{option:!dataGrid}<p>{$msgNoCategoryItems|sprintf:{$var|geturl:'add_category'}}</p>{/option:!dataGrid}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}
