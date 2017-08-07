jQuery(document).ready(function($){
	
	$("#_hana_block_type").change(function(){
		Hana_Block_Type( $(this).val() );
	});
	Hana_Block_Type( $("#_hana_block_type").val() );
    
	function Hana_Block_Type( blocktype ){
        if ( '' == blocktype ) {
            $("#p_hana_block_layout").hide();
        } else {
             $("#p_hana_block_layout").show();           
        }
	}
	
});
