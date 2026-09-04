import { initializeApp } from 'firebase/app';
import { getAuth, GoogleAuthProvider, signInWithPopup } from 'firebase/auth';

const firebaseConfig = {
    apiKey: 'AIzaSyDUgh_H5PIeKxjbsAFL5Uw_KckbHw8rA0g',
    authDomain: 'task-management-eac91.firebaseapp.com',
    projectId: 'task-management-eac91',
    storageBucket: 'task-management-eac91.firebasestorage.app',
    messagingSenderId: '129453722894',
    appId: '1:129453722894:web:777707127d40617859db5f',
};

initializeApp(firebaseConfig);

const auth = getAuth();
const provider = new GoogleAuthProvider();

const form = document.getElementById('firebase-form');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;

    try {
        const result = await signInWithPopup(auth, provider);
        const idToken = await result.user.getIdToken();

        const tokenInput = form.querySelector('input[name="id_token"]');
        tokenInput.value = idToken;
        form.submit();
    } catch (error) {
        button.disabled = false;
        alert('Login gagal: ' + error.message);
    }
});