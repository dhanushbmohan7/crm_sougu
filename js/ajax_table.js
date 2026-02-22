$(document).ready(function() {
	//alert("this script is working");
    // Setup - add a text input to each footer cell
	
    $('#dynamic_ajax_table tfoot th').each( function () {
        var title = $(this).text();
		if(title!="Edit" && title!="Remove" && title!="Print" && title!="ProfilePic" && title!="view")
		{
        $(this).html( '<input type="text" placeholder="Search" />' );
		}
		else{
		$(this).html('');	
		}
    } );
 
    // DataTable
    var table = $('#dynamic_ajax_table').DataTable({
		dom: 'lBfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'colvis'
        ],
		"bLengthChange": true,
		"bSort" : true,
		 "lengthMenu": [[ 20, 50,75,100, -1], [ 20, 50,75,100, "All"]],
		 "pagingType": "full_numbers",
        columnDefs: [ {
            visible: false
        } ]
		
        
    });
	
	
	
	var node = document.getElementsByClassName("dt-buttons")
   var textnode = document.getElementById("print_btn_chk");
    var textnode2 = document.getElementById("print_btn_dos_chk");
  document.getElementsByClassName("dt-buttons")[0].appendChild(textnode);
   document.getElementsByClassName("dt-buttons")[0].appendChild(textnode2);
	
	
 
    // Apply the search
    table.columns().every( function () {
        var that = this;
 
        $( 'input', this.footer() ).on( 'keyup change', function () {
            if ( that.search() !== this.value ) {
				
                that.search( this.value ).draw(); 
				
            }
        } );
    } );
	
	
	 table.on( 'order.dt search.dt', function () {
        table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );
    } ).draw();
	
	
} );

/*$(document).ready(function() {
    $('#dynamic_ajax_table').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    } );
} );*/