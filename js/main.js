function form_validate()
{ 
    val = document.getElementById('query').value;
    val_arr = val.split(" ");

    for(i=0;i<val_arr.length;i++)
        if(val_arr[i].length<=3)
        {
            return false;
        }
}
function auto_complete(val)
{
    val_arr = val.split(" ");
    
    for(i=0;i<val_arr.length;i++){
        if(val_arr[i].length<=3)
        {
            return false;
        }
    }
    
    var index = document.getElementById('indexType').value;   
    $.ajax({
        type: "POST",
        url: "auto_search.php",
        data: { query: val, indexType: index },
        success: function( data ) {
            $('#place_holder').html(data);
        }
    });
}
