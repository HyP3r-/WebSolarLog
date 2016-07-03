{{#data.tables}}
<button type="button" id="btn_dbm_table_{{this}}" class="dbm_select" style="width:100%;">{{this}}</button><br/>
{{/data.tables}}
<script>
$('.dbm_select').bind('click', function () {
	tableName = $(this).attr('id').replace('btn_dbm_table_', '');
	$('#page-title').text(tableName);

	$.getJSON("admin-server.php?s=dbm_getTableData&tableName="+tableName, function(json){
		var options = {
        		    enableCellNavigation: true,
        		    enableColumnReorder: true,
        		    autoEdit: false,
        		    editable: true
        		  };
        
        var columns = [];
        $.each(json.columns, function() {
        	if (this.editor == "Slick.Editors.Text") {
        		this.editor = Slick.Editors.Text;
        	}
        	if (this.editor == "Slick.Editors.Integer") {
        		this.editor = Slick.Editors.Integer;
        	}
        
        	columns.push(this);
        });
        		  
		dbm_grid = new Slick.Grid("#dbtable", json.data, columns, options);
		
		dbm_grid.onCellChange.subscribe(function(e,args){
    		$.post("admin-server.php?s=dbm_saveTableData&tableName=" + $('#page-title').text(), $.param(args.item), function(data){
    			// TODO check if succes
    		});
		});
	});

});

</script>