<script>
(function () {
    var KEY = 'gs_lang';
    var lang = localStorage.getItem(KEY);
    if (lang !== 'vi' && lang !== 'en') {
        lang = 'vi';
        localStorage.setItem(KEY, lang);
    }
    var match = document.cookie.match(/(?:^|;\s*)gs_lang=([^;]+)/);
    var cookieLang = match ? match[1] : null;
    if (cookieLang !== lang) {
        document.cookie = 'gs_lang=' + lang + ';path=/;max-age=31536000;SameSite=Lax';
        if (document.documentElement.lang !== lang) {
            location.reload();
        }
    }
})();
</script>
