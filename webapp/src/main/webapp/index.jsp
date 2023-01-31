<h1 style="color:green">
    GeeksforGeeks
</h1>
<h2>DOM Location hostname Property</h2>
<p>
    For returning the hostname of the current
    URL, double click the "Return hostname" button:
</p>
<button ondblclick="myhost()">
    Return hostname
</button>
<p id="hostname"></p>
<script>
    function myhost() {
        var h = location.hostname;
        document.getElementById("hostname").innerHTML = h;
    }
</script>
