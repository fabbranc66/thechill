<?php 

/* HEADER NO CACHE */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


include "config.php"; ?>
<h2>Classifica</h2>
<div id="classifica"></div>
<script>
setInterval(()=>{
fetch("ajax_classifica.php")
.then(r=>r.text())
.then(d=>{ document.getElementById("classifica").innerHTML=d; });
},2000);
</script>