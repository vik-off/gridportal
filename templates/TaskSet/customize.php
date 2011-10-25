
<h2>Файлы профиля</h2>
<div class="task-uploaded-files">
	<div style="font-weight: bold; text-align: center; margin-bottom: 10px;"><?= Lng::get('upload_files.uploadfiles'); ?></div>
	<form action="" method="post" enctype="multipart/form-data">
		<?= FORMCODE; ?>	
		<input type="hidden" name="action" value="task-set/upload-file" />
		<input type="hidden" name="id" value="<?= $this->instanceId; ?>" />
		<input type="file" name="Filedata" />
		<input type="submit" value="Отправить" />
	</form>
</div>

<div class="task-uploaded-files">
	<div style="font-weight: bold; text-align: center; margin-bottom: 10px;"><?= Lng::get('upload_files.sendfileslist'); ?></div>
	<div id="task-uploaded-files-container"></div>
	<div id="task-uploaded-files-comment" style="margin-top: 1em;"></div>
</div>

<div class="paragraph">

	<a href="<?= href('task-set/submit/'.$this->instanceId); ?>" class="button">Перейти к запуску</a>
	<a href="<?= href('task/list') ?>" class="button">Вернуться к списку</a>
</div>

<script type="text/javascript">
$(function() {
	
	var FileManager = {
		
		getUrl: href('task-set/get-task-files/<?= $this->instanceId; ?>'),
		delUrl: href('task-set/delete-task-file/<?= $this->instanceId; ?>'),
		htmlContainer: $('#task-uploaded-files-container'),
		htmlComment: $('#task-uploaded-files-comment'),
		
		hasNordujob: false,
		
		getFiles: function(){
			
			var self = this;
			
			$.get(this.getUrl, function(response){
				
				if(response.error){
					self.htmlContainer.empty().html('<div style="color: red;">' + response.error + '</div>');
					return;
				}
				
				var tbl = $('<table class="task-uploaded-files-list" />');
				var delLink = null;
				for(var i in response.data){
				
					type = '';
					if(response.data[i] == 'nordujob'){
						type = 'nordujob файл';
						self.hasNordujob = true;
					}
					else if(/\.fds$/.test(response.data[i]))
						type = 'файл модели';
						
					delLink = (function(file){
						return $('<a href="#" class="small">удалить</a>')
							.click(function(){ self.removeFile(file); return false; });
					})(response.data[i]);
					
					tbl.append(
						$('<tr />')
							.append('<td>' + (type ? '<span class="small" style="color: #888;">' + type + '</span>' : '') + '</td>')
							.append('<td>' + response.data[i] + '</td>')
							.append($('<td></td>').append(delLink)));
				}
				
				self.htmlContainer.empty().append(tbl);
				
				if(self.hasNordujob){
					self.htmlComment.html('<span class="small green">Файл nordujob загружен</span>');
				}else{
					self.htmlComment.html('<span class="small red">Файл nordujob не загружен</span>');
				}
					
			}, 'json');
		},
		
		removeFile: function(name){
			
			if(!confirm('Удалить файл "' + name + '"?'))
				return;
			
			var self = this;
			
			$.post(this.delUrl, {file: name}, function(response){
				
				if(response != 'ok')
					alert(response);
					
				self.getFiles();
			});
		}
	};
	
	FileManager.getFiles();
	
});
</script>
