(function ($) {
    $(document).ready(function () {
        var fileNode = document.getElementById("file").parentNode;
        var tagDiv = document.getElementById("tagContainer");

        fileNode.parentNode.insertBefore(tagDiv, fileNode.nextSibling);
    })
})(jQuery);
