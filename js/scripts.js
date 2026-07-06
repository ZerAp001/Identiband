function filterSelection(c) {
    let items = document.getElementsByClassName("product-item");

    if (c === "Todos") {
        c = "";
    }

    for (let i = 0; i < items.length; i++) {
        let item = items[i];

        if (item.className.indexOf(c) > -1) {
            item.style.display = "";
        } else {
            item.style.display = "none";
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    filterSelection("Todos");
});