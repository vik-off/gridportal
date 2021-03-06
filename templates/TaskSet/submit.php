<script type="text/javascript" language="javascript">

$(document).ready(function() {

	jQuery.fn.center = function () {
		this.css("position","fixed");
		this.css("top", ( $(window).height() - this.height()) / 2 + "px");
		this.css("left", ( $(window).width() - this.width()) / 2 + "px");
		return this;
	}


    $(window).resize(function() {
        $(".popup-container").css({width: $(this).width() + 'px', height: $(this).height() + 'px'});
    });

    $("input[type=submit]").click(function() {
		$(window).resize();
		$(".popup").center();
		$(".popup .body-container").center();
		$("#wait-popup").show();
    });


});
</script>

<h2 style="vertical-align:bottom;">
	<img src="/images/icons/castom.gif" alt="<?= Lng::get('xrls_edit.taskset') ?>" title="<?= Lng::get('xrls_edit.taskset') ?>" align="center" width=32 height=32 onmouseover="this.src='/images/icons/castom.a.gif'" onmouseout="this.src='/images/icons/castom.gif'" />
	<?= Lng::get('xrls_edit.taskset') ?>
</h2>
<div class="c">
<form action="" method="post">
	<?= FORMCODE; ?>
	<input type="hidden" name="id" value="<?= $this->id; ?>" />
	
	<?= $this->myproxyLoginForm; ?>
	
	<table style="margin: 1em auto; text-align: left;">
	<tr>
		<td><?= Lng::get('xrls_edit.preferred-server') ?><br /><?= Lng::get('xrls_edit.take-empty-if-not-requred') ?></td>
		<td><input type="text" name="prefer-server" value="" /></td>
	</tr>
	<tr>
		<td>Оповещать по email</td>
		<td><input type="checkbox" name="email-notify" value="1" <?= $this->taskFetchNotify ? 'checked="checked"' : ''; ?> /></td>
	</table>
	
	<div class="paragraph">
		<?= Lng::getDeclinated('task-set.will-run-tasks', $this->numSubmits, array($this->numSubmits)); ?>
	</div>
	<div class="paragraph">
		<input class="button" type="submit" name="action[task-set/submit]" value="<?= Lng::get('xrls_edit.start-task'); ?>" />
		<a class="button" href="<?= href('task-set/customize/'.$id); ?>"><?= Lng::get('xrls_edit.manage-files'); ?></a>
		<a class="button" href="<?= href('task-set/list'); ?>"><?= Lng::get('xrls_edit.go-to-task-list'); ?></a>
	</div>
    
    <div id="wait-popup" class="popup-container" style="display:none">
	<div class="popup">
		<div class="body-container">		
			<img src="images/load.gif" alt="<?= Lng::get('xrls_edit.starting-task'); ?>" />
			<p><?= Lng::get('xrls_edit.starting-task'); ?></p>
		</div>
	</div>
</div>
</form>
</div>