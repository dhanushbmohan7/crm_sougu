function fetch_caste(i,j)
{

var check;

if($("input[id='"+ j +"']:checked").length > 0)
	{		
		
		 
		 check=0;
		 
		
	}
	else
	{
		 check=1;
	}
		
$.ajax({
                type: 'POST',
                url: 'fetch_set_theme.php',
                data: {
					row_id: i,
					row_check: check
					                  
                },
                error: function (request, error) {
                    alert("Please Wait, Loading...");
                },
                success: function (response) {	
				window.location.href='theme.php'	
				}
            });
}