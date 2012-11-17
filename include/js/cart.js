function addToCart(id){
    //alert(id);
    var tmp = ''; 
    $.ajax({
                    type: "POST",
                    data: $("#catalog"+id).serialize() ,
                    url: "/addToCart/",
                    success: function(msg){
                    //alert(msg);
                    $("#cart").html( msg );
                    $("#rez"+id).html(tmp);
                    },
                    beforeSend : function(){
                        //$("#sss").html("");
                        tmp = $("#rez"+id).html();
                        $("#rez"+id).html('<div id="noTrespassingOuterBarG"><div id="noTrespassingFrontBarG" class="noTrespassingAnimationG"><div class="noTrespassingBarLineG"></div><div class="noTrespassingBarLineG"></div><div class="noTrespassingBarLineG"></div><div class="noTrespassingBarLineG"></div><div class="noTrespassingBarLineG"></div><div class="noTrespassingBarLineG"></div></div></div>');                    
                    }
                    });
}


