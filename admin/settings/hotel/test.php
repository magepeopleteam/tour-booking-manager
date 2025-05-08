<?php
 ?>

<style>

    /*Hotel List Search Item Wrapper*/

    .titleSearchHolder{
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        position: relative;
        padding: 2px;
        width: 100%;
        border: 1px solid #ccc;
        background-color: #FFFFFF;
    }
    .removeSelectedItems{
        width: 25px;
        text-align: center;
        margin-top: 14px;
    }
    .titleSearchContainer{
        display: block;
        width: calc( 100% - 25px );
    }
    #productTitleSearchBox{
        background: none;
        border: none;
        outline: none;
        font-size: 1em;
        margin: 2px;
        padding: 4px 0px;
        vertical-align: middle;
        width: 190px;
    }
    #productTitleSearchBox:focus{
        box-shadow: 0 0 0 0;
    }
    .searchResultContainer{
        display: block;
    }
    .productTitleHolder{
        padding: 5px 2px 5px 5px;
        margin: 5px;
        border: 1px solid #d9d1d1;
        border-radius: 3px;
        float: left;
    }
    .removeSingleProduct{
        margin-left: 5px;
        cursor: pointer;
        padding: 5px;
        background-color: #ddd4d4;
        color: #f4f4f4;
    }
    .productDropDownMenu{
        width: 100%;
        display: none;
        /*border-color: #b3b3b3 #ccc #d9d9d9;*/
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
        background: #fff;
        border: 1px solid #ccc;
        margin-top: -1px;
        box-sizing: border-box;
        overflow: auto;
        position: absolute;
        max-height: 200px;
        z-index: 10;
    }
    .productDropDownMenu .option-wrapper {
        cursor: pointer;
        outline: none;
    }

    .productDropDownMenu .option-wrapper .productTitleSelect {
        color: #666;
        cursor: pointer;
        padding: 8px 10px;
    }

    .productDropDownMenu .option-wrapper .productTitleSelect:hover {
        background-color: #b2d2ee;
    }

    .rmSingleMultipleBtnHOlder{
        display: flex;
        padding: 10px;
    }
    .rmChangeSingleMultiple {
        border: 0;
        cursor: pointer;
        display: inline-block;
        font-family: system-ui, -apple-system, system-ui, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", sans-serif;
        font-size: 16px;
        outline: 0;
        padding: 10px 15px;
        position: relative;
        text-align: center;
        text-decoration: none;
        transition: all .3s;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
        margin: 0 3px 0 3px;
    }

    .borderRadiusMultiple{
        border-top-left-radius : 10px;
        border-bottom-left-radius : 10px;
    }
    .borderRadiusSingle{
        border-top-right-radius : 10px;
        border-bottom-right-radius : 10px;
    }

    .rmBackgroundColorSelected{
        background-color: #990000;
        color: #fff;
    }
    .rmBackgroundColor{
        background-color: #e8ebee;
        color: #444444;
    }

</style>
<div class="ttbm_total_booking_filter_controls">
    <div data-collapse="#ttbm_hotel_id" class="mActive">
        <div class="titleSearchHolder">
            <div class="titleSearchContainer">
                <div class="searchResultContainer" id="searchResultContainer"></div>
                <input type="text" class="productTitleSearchBox" id="productTitleSearchBox" placeholder="Product Name Search...">
                <div class="productDropDownMenu" id="productDropDownMenu">
                    <div class="option-wrapper" id="productTitleWrapper"></div>
                </div>
            </div>
            <div class="removeSelectedItems">X</div>
        </div>
    </div>
</div>

<script>
    function display_search_data( productTitles ) {
        jQuery("#productTitleWrapper").children().remove();
        let length = productTitles.length;
        let titleText = ''; // Use a string to accumulate HTML content
        for (let i = 0; i < length; i++) {
            titleText += '<div class="productTitleSelect" id="productTitleSelect-'+productTitles[i]['id']+'">\
                                  <span class="productTitleClicked" id="productTitle-'+productTitles[i]['id']+'">' + productTitles[i]['id'] + '::' + productTitles[i]['title'] + '</span>\
                              </div>';
        }

        return titleText;
    }

    function get_search_data_and_display( setUrl, type, search_term, nonce){
        jQuery.ajax({
            type: type,
            url: setUrl,
            data: {
                action: "get_ttbm_hotel_search_by_title",
                search_term: search_term,
                limit: 10 // Adjust the limit as needed
            },
            success: function( response ) {

                console.log( response );

                let searchData =  response.data.result_data;
                $("#ttbm_hotel_lists_listview").html( searchData );
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });

    }

    $(document).on('click', '.productTitleSelect', function() {
        let clickedId = jQuery(this).attr('id');
        let productID = clickedId.replace('productTitleSelect-', '').trim();
        let productTitle = jQuery(this).children().text();
        let selectedProductTitle = '';
        if( productTitle.length > 0 ){
            selectedProductTitle = '<div class="productTitleHolder" id="productTitleHolder-'+productID+'">\
                                                <span class="productTitle" id="'+productID+'">'+productTitle+'</span>\
                                                <span class="removeSingleProduct" id="removeSingleProduct-'+productID+'">x</span>\
                                            </div>';
            jQuery("#searchResultContainer").append( selectedProductTitle );
            jQuery('#'+clickedId).hide();
        }
    });

    $(document).on('click', '.removeSingleProduct', function() {
        let removeProductClickedID = jQuery(this).attr('id');
        let removeProductID = removeProductClickedID.replace('removeSingleProduct-', '').trim();
        let selectedProductId = 'productTitleSelect-'+removeProductID;
        let parentDivID = 'productTitleHolder-'+removeProductID;
        jQuery("#"+parentDivID).remove();
        jQuery("#"+selectedProductId).show();
    });

    $('#productTitleSearchBox').on('input', function() {
        let search_term = jQuery(this).val();
        let nonce = ttbm_admin_ajax.nonce;
        let setUrl = ttbm_ajax_url;
        let type = 'POST';
        if( search_term.length > 0 ) {
            jQuery("#productTitleWrapper").show();
            jQuery("#productDropDownMenu").show();
        }
        if( search_term.length === 3 ) { // Trigger search when input length is more than 2
            get_search_data_and_display( setUrl, type, search_term, nonce);
        }else if( search_term.length === 12 ){
            get_search_data_and_display( setUrl, type, search_term, nonce);
        }else if( search_term.length === 22 ){
            get_search_data_and_display( setUrl, type, search_term, nonce);
        }else if( search_term.length === 33 ){
            get_search_data_and_display( setUrl, type, search_term, nonce);
        }
        else if( search_term.length < 3 ){
            search_term = '';
            get_search_data_and_display( setUrl, type, search_term, nonce);
        }
    });
</script>
