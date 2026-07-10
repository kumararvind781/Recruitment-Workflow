document.addEventListener('change', function(e){
  if(e.target.matches('[data-copy-link]')){
    navigator.clipboard.writeText(e.target.value).catch(()=>{});
  }
});
