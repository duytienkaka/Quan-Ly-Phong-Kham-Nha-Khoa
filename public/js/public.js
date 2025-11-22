// Generic lightweight nav toggle for mobile
(function(){
    var toggles = document.querySelectorAll('.nav-toggle');
    if(!toggles || toggles.length === 0) return;

    toggles.forEach(function(btn){
        var targetSel = btn.getAttribute('data-target');
        if(!targetSel) return;
        var target = document.querySelector(targetSel);
        if(!target) return;

        btn.addEventListener('click', function(e){
            var expanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!expanded));
            target.classList.toggle('open');
        });

        // close when clicking outside
        document.addEventListener('click', function(e){
            if(!target.classList.contains('open')) return;
            var isInside = target.contains(e.target) || btn.contains(e.target);
            if(!isInside){
                target.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
            }
        });
    });
})();
