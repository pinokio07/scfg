<!-- Modal Upload -->
<div class="modal fade" id="modal-upload">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Upload</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form id="formUpload"
            class="form-horizontal" 
            action="{{$action}}" 
            method="POST" 
            enctype="multipart/form-data">
				@csrf			
				<div class="modal-body">
					<div class="form-group">
						<label>Pilih File</label>
						<input type="file" class="form-control" name="upload" required="required" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
					</div>							
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Upload</button>
				</div>
			</form>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->