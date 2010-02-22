{include:file='{$BACKEND_CORE_PATH}/layout/templates/header.tpl'}
{include:file='{$BACKEND_CORE_PATH}/layout/templates/sidebar.tpl'}
			<td id="contentHolder">
				<div id="statusBar">
					<p class="breadcrumb">{$lblSnippets|ucfirst} &gt; {$msgEditWithItem|sprintf:{$title}}</p>
				</div>
				<div class="inner">
					{form:edit}
						<p>
							<label for="title">{$lblTitle|ucfirst}</label>
							{$txtTitle} {$txtTitleError}
						</p>

						<div class="tabs">
							<ul>
								<li><a href="#tabContent">{$lblContent|ucfirst}</a></li>
								<li class="notImportant"><a href="#tabRevisions">{$lblRevisions|ucfirst}</a></li>
							</ul>

							<div id="tabContent">
								<fieldset>
									<label for="content">{$lblContent|ucfirst}</label>
									<p>{$txtContent} {$txtContentError}</p>
									<p><label for="hidden">{$chkHidden} {$chkHiddenError} {$msgVisibleOnSite}</label></p>
								</fieldset>
							</div>

							<div id="tabRevisions">
								<div class="datagridHolder">
									<div class="tableHeading">
										<div class="oneLiner">
											<h3 class="floater">{$lblRevisions|ucfirst}</h3>
											<abbr class="help floater">(?)</abbr>
											<div class="balloon balloonAlt" style="display: none;">
												<p>{$msgHelpRevisions}</p>
											</div>
										</div>
									</div>

									{option:revisions}{$revisions}{/option:revisions}
									{option:!revisions}
										<table border="0" cellspacing="0" cellpadding="0" class="datagrid">
											<tr>
												<td>
													{$msgNoRevisions}
												</td>
											</tr>
										</table>
									{/option:!revisions}
								</div>
							</div>
						</div>

						<div class="fullwidthOptions">
							<a href="{$var|geturl:'delete'}&id={$id}" rel="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
								<span><span><span>{$lblDelete|ucfirst}</span></span></span>
							</a>

							<div class="buttonHolderRight">
								<input id="edit" class="inputButton button mainButton" type="submit" name="edit" value="{$lblEdit|ucfirst}" />
							</div>
						</div>

						<div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
							<p>
								<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
								{$msgConfirmDelete|sprintf:{$title}}
							</p>
						</div>
				{/form:edit}
			</div>
		</td>
	</tr>
</table>
{include:file='{$BACKEND_CORE_PATH}/layout/templates/footer.tpl'}