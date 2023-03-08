(function ($) {
    $(document).ready(function () {
        const fileNode = document.getElementById("file").parentNode;
        const tagDiv = document.getElementById("tagContainer");

        fileNode.parentNode.insertBefore(tagDiv, fileNode.nextSibling);
    })
})(jQuery);
