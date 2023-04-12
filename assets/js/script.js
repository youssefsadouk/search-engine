var timer;

$(document).ready(function(){
    
    $(".result").on("click", function(){
        var url = $(this).attr("href");
        var id = $(this).attr("data-id");
        console.log(id);

        increaseLinkClicks(id, url);
        return false;
    })

    var grid = $(".imageResults");

    grid.on("layoutComplete", function(){
        $(".gridItem img").css("visibility", "visible");
    })
    grid.masonry({
        itemSelector: ".gridItem", 
        columnWidth: 200,
        gutter: 5,
        isInitLayout: false
    });

    $("[data-fancybox]").fancybox(); 
    
})
function loadImage(src, className){
    var image = $("<img>");
    image.on("load", function(){
        $("." + className + " a").append(image);
        clearTimeout(timer);

        timer = setTimeout(function(){
            $(".imageResults").masonry();
        }, 500)
    });
    image.on("error", function(){
        $("." + className).remove();

        $.post("ajax/markAsBroken.php", {src: src});
    })

    image.attr("src", src);
}

function increaseLinkClicks(id, url){
    $.post("ajax/linkClicksUpdate.php", {id: id})
    .done(function(res){
        if (res != ""){
            console.log(res);
            return;
        }

        window.location.href = url;
    });
} 