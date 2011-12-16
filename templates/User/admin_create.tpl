
<form id="edit-form" action="" method="post">
	{$formcode}
	
	<table class="stdItemEdit">
	<tr>
		<th class="title" colspan="2">
			{if !$instanceId}
				Создание новой записи
			{else}
				Редактирование записи #{$instanceId}
			{/if}
		</th>
	</tr>
	<tr>
		<th>email</th>
		<td><input type="text" name="email" value="{$email}" /></td>
	</tr>
	<tr>
		<th>Пароль</th>
		<td><input type="text" name="password" value="{$password}" /></td>
	</tr>
	<tr>
		<th>Фамилия</th>
		<td><input type="text" name="surname" value="{$surname}" /></td>
	</tr>
	<tr>
		<th>Имя</th>
		<td><input type="text" name="name" value="{$name}" /></td>
	</tr>
	<tr>
		<th>Отчество</th>
		<td><input type="text" name="patronymic" value="{$patronymic}" /></td>
	</tr>
	<tr>
		<th>Права</th>
		<td><input type="text" name="level" value="{$level}" /></td>
	</tr>
	<tr>
		<th>Активация</th>
		<td><input type="text" name="active" value="{$active}" /></td>
	</tr>
	<tr>
		<th>Действия</th>
		<td class="actions">
		
			<input class="button" type="submit" name="action[user/create]" value="Сохранить" />
			<a class="button" href="{a href=admin/users}">отмена</a>
			
			<div class="after-action">
				+ 
				<select name="redirect" id="next-action-select">
					<option value="{a href=admin/users}">К адм. списку записей</option>
					<option value="{a href=admin/users/edit/$instanceId}">Продолжить редактирование</option>
					<option value="{a href=admin/users/new}">Создать новую запись</option>
				</select>
			</div>
			
		</td>
	</tr>
	<tr>
		<td class="footer" colspan="2">
			<a href="mailto:yurijnovikov@gmail.com" title="Разработчик: Юрий Новиков">vik-off CMF</a>
		</td>
	</tr>
	</table>
</form>

<script type="text/javascript">

$(function(){
	
	$("#next-action-select").val("{{$redirect}}");
	
	$("#edit-form").validate({{$validation}});

});

</script>
