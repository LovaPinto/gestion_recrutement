const parMois=document.getElementById('parMois');
const formparMois=document.getElementById('formparMois');
const parSemaine=document.getElementById('parSemaine');

parMois.addEventListener('click', function() {
    if (formparMois.style.display === 'none') {
        formparMois.style.display = 'flex'
        parMois.classList.add('active')
        parSemaine.classList.remove('active')
    } else {
        formparMois.style.display = 'none'
        parMois.classList.remove('active')
        parSemaine.classList.add('active')
    }
})

parSemaine.addEventListener('click', function() {
    if (formparMois.style.display === 'flex') {
        formparMois.style.display = 'none'
        parSemaine.classList.add('active')
        parMois.classList.remove('active')
    }
})

