<div>
	������ <?= $this->jobid; ?> (��� � ������� <?= $this->task_name; ?>)
	��������� �� �������� <?= Lng::get()->getLngSnippet($this->lng, $this->task_status); ?><br />
	������ �� ������: <a href="<?= $this->task_href; ?>"><?= $this->task_href; ?></a>
</div>