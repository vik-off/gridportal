
<!-- PLAIN TEXT ERROR DESCRIPTION

<?=$PLAIN_TEXT;?>
     PLAIN TEXT ERROR DESCRIPTION  -->
	
	
<div class="error-all <?=$MODE;?>">
	
	<div class="error-managment">
		<? if($MODE == 'display-mode'): ?>
			#<?=$DB_ID;?>
			<form class="inline" action="" method="post" onsubmit="return confirm('Удалить?');">
				<input type="hidden" name="id" value="<?=$DB_ID;?>" />
				<?=FORMCODE;?>
				<input type="submit" class="button-small" name="action[core/error-delete-item]" value="удалить" />
			</form>
		<? else: ?>
			<a href="#" onclick="this.parentNode.parentNode.style.display = 'none'; return false;" class="button-small">Закрыть</a>
		<? endif; ?>
	</div>
	
	<span class="error-level"><?=$ERROR_LEVEL;?></span>: <?=$ERROR_STRING;?> in <strong><?=$ERROR_FILE;?></strong> on line <?=$ERROR_LINE;?>.<br />
	
	<div class="error-stack-trace">
		<?=$BACKTRACE;?>
	</div>
	
	<div class="error-meta-info">
		Время возникновения: <?=$ERROR_TIME;?><br />
		Запрашиваемый URL: <a href="<?=$ERROR_URL;?>"><?=$ERROR_URL;?></a>
	</div>

</div>
