document.querySelector('#loading').style.display = 'none';
document.querySelector('#error').style.display = 'none';

let prevValue = '';

document.querySelector('input').addEventListener('input', () => {
    document.querySelector('input').value = document.querySelector('input').value.replace(/\D/g, '');
    document.querySelector('#error').style.display = 'none';
});


document.querySelector('form').addEventListener('submit', (e)=>{
    e.preventDefault();
    submit(document.querySelector('input').value);
});
document.querySelector('a[type="submit"]').addEventListener('click', ()=>{
    submit(document.querySelector('input').value);
});

function submit(value){
    // Make sure the number only contains numbers
    if(!/^\d+$/.test(value)){
        document.querySelector('#error').style.display = '';
        document.querySelector('#error').textContent = 'Code must contain only numbers';

        return;
    }
    if(value.length != 6){
        document.querySelector('#error').style.display = '';
        document.querySelector('#error').textContent = 'Code must be 6 digits long';

        return;
    }

    document.querySelector('#loading').style.display = '';
    document.querySelector('input').readOnly = 'true';
    document.querySelector('input').style.color = '#ababab';

    fetch('/api/user/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ login: value })
    }).then(response => {
        if (!response.ok) {
            document.querySelector('#loading').style.display = 'none';
            document.querySelector('input').readOnly = '';
            document.querySelector('input').style.color = '';

            document.querySelector('#error').style.display = '';
            document.querySelector('#error').textContent = 'An error has occoured, try again later';

            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if(data.status == 'error'){
            document.querySelector('#loading').style.display = 'none';
            document.querySelector('input').readOnly = '';
            document.querySelector('input').style.color = '';

            document.querySelector('#error').style.display = '';
            document.querySelector('#error').textContent = data.message;
        }
        if(data.status == 'success'){
            setCookie('session', data.user.session, 183);
            fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
              document.open();
              document.write(html);
              document.close();
            })
            .catch(error => console.error('Error fetching the page:', error));
          
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000)); // Calculate expiration time
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`; // Set cookie
}