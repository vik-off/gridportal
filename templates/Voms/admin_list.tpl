
<div class="options-row">
	{a href=admin/content/voms/new text="Добавить запись"}
</div>

{$pagination}

{if $collection}
	<table class="std-grid tr-highlight">
	<tr>
		<th>{$sorters.id}</th>
		<th>{$sorters.name}</th>
		<th>{$sorters.url}</th>
		<th>Опции</th>
	</tr>
	{foreach from=$collection item='item'}
	<tr>
		<td>{$item.id}</td>
		<td>{$item.name}</td>
		<td>{$item.url}</td>
		<td class="center">
			<div class="tr-hover-visible options">
				<a href="{a href=voms/view/`$item.id`}" class="item" title="Просмотреть"><img src="images/backend/icon-view.png" alt="Просмотреть" /></a>
				<a href="{a href=admin/content/voms/edit/`$item.id`}" class="item" title="Редактировать"><img src="images/backend/icon-edit.png" alt="Редактировать" /></a>
				<a href="{a href=admin/content/voms/delete/`$item.id`}" class="item" title="Удалить"><img src="images/backend/icon-delete.png" alt="Удалить" /></a>
			</div>
		</td>
	</tr>
	{/foreach}
	</table>
{else}
	<p>Сохраненных записей пока нет.</p>
{/if}

{$pagination}

