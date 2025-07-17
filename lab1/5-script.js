window.addEventListener("load", function(){
  for (let book of document.getElementsByClassName("book-wrap")) {
    book.addEventListener("click", function(){
      var id = this.dataset.id,
          name = this.getElementsByClassName("book-title")[0].innerHTML,
          desc = this.getElementsByClassName("book-desc")[0].innerHTML;
      alert(`You have selected - ID: ${id}, TITLE: ${name} DESC: ${desc}`);
    });
  }
});