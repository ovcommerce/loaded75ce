<!--<div class="crumbs">
    <ul id="breadcrumbs" class="breadcrumb">
        <li>
            <i class="icon-home"></i>
            <a href="{$app->urlManager->createUrl("index")}">Dashboard</a>
        </li>
        {foreach $context->navigation as $item_navigation}
        <li class="current">
            <a href="{$item_navigation['link']}" title="">{$item_navigation['title']}</a>
        </li>
        {/foreach}
    </ul>
    <ul class="crumb-buttons">
        <li><a href="charts.html" title=""><i class="icon-signal"></i><span>Statistics</span></a></li>
        <li class="dropdown"><a href="#" title="" data-toggle="dropdown"><i class="icon-tasks"></i><span>Users <strong>(+3)</strong></span><i class="icon-angle-down left-padding"></i></a>
            <ul class="dropdown-menu pull-right">
                <li><a href="form_components.html" title=""><i class="icon-plus"></i>Add new User</a></li>
                <li><a href="tables_dynamic.html" title=""><i class="icon-reorder"></i>Overview</a></li>
            </ul>
        </li>
        <li class="range"><a href="#">
                <i class="icon-calendar"></i>
                <span></span>
                <i class="icon-angle-down"></i>
            </a></li>
    </ul>
</div>!-->
<div class="top_bead after">
{if $context->navigation}
{foreach $context->navigation as $item_navigation}
	<h1>	
		{$item_navigation['title']}
	</h1>
{/foreach}
	{else}
		<h1>{$smarty.const.TEXT_DASHBOARD}</h1>
	{/if}
{foreach $context->topButtons as $topButton}
	{$topButton}
{/foreach}
</div>