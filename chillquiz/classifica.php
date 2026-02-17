<?php include "config.php"; ?>
<h2>Classifica</h2>
<div id="classifica"></div>
<script>
setInterval(()=>{
fetch("ajax_classifica.php")
.then(r=>r.text())
.then(d=>{ document.getElementById("classifica").innerHTML=d; });
},2000);
</script>