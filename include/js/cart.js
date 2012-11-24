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

function ajaxRemoveProduct(id){
    $.ajax({
        type: "POST",
        data: "&id="+id ,
        url: "/del_pos/",
        success: function(msg){
            //alert(msg);
            $("#content2Box").html( msg );
            ShowCart();
        },
        beforeSend : function(){
            $("#content2Box").html( '<div class="FonLoarder"><img src="/images/design/loading.gif" alt="" /></div>');
        }
    });
}

function ShowCart(){
    $.ajax({
        type: "POST",
        url: "/showCart/",
        success: function(msg){
            $("#cart").html( msg );
        },
        beforeSend : function(){
            $("#cart").html( '<div id="noTrespassingOuterBarG"><div id="noTrespassingFrontBarG" class="noTrespassingAnimationG"><div class="noTrespassingBarLineG"></div><div class="noTrespassingBarLineG"></div><div class="noTrespassingBarLineG"></div><div class="noTrespassingBarLineG"></div><div class="noTrespassingBarLineG"></div><div class="noTrespassingBarLineG"></div></div></div>' );
        }
    });
} 
function me(e)
{
    /*if(e.charCode==8){alert('good');}*/
    if((e.charCode>47&&e.charCode<58)||e.keyCode==8 ||e.keyCode==37 ||e.keyCode==39){
        return true;
    }else
        return false;
}
function prov0(id){
    val=id.val();
    if(val=='0'){
        id.val(1);
    }
}

function pereshot(id){
    prov0($('#quantity'+id));
    val = parseInt($('#quantity'+id).val());
    //alert(val);
    if(val=='' || isNaN(val)) return false;
    
    valTmp = parseInt($('#quantityTmp'+id).val());//Старое количество
    $('#quantityTmp'+id).val(val);//Обновляэм староэ количество
    
    price = parseInt($('#price'+id).html());
    sum = parseInt(price * val);
    sumTmp = parseInt(valTmp * price);
    
    $('#sum'+id).html(sum+' грн.');
    allSumTmp = parseInt($('#priceALL').html());
    allSum = parseInt(allSumTmp - sumTmp + sum);
    //alert('sum='+sum+' sumTmp='+sumTmp+' allSumTmp='+allSumTmp+' allSum='+(allSumTmp - sumTmp + sum));
    $('#priceALL').html(allSum+' грн.');
    updateCart(id,val);
}

function updateCart(id,val){
    $.ajax({
        type: "POST",
        data: "&id="+id+"&quantity="+val ,
        url: "/updateCart/",
        success: function(msg){
            //alert(msg);
            //$("#content2Box").html( msg );
            ShowCart();
        }/*,
        beforeSend : function(){
            $("#content2Box").html( '<div class="FonLoarder"><img src="/images/design/loading.gif" alt="" /></div>');
        }*/
    });
}

