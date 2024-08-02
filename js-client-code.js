let isAdmin = true;
let currentQueue = [];
let currentPatient = null;

document.addEventListener('DOMContentLoaded', () => {
    const addPatientForm = document.getElementById('add-patient-form');
    const toggleViewButton = document.getElementById('toggle-view');
    const adminPanel = document.getElementById('admin-panel');
    const patientView = document.getElementById('patient-view');
    const signinForm = document.getElementById('signin-form');

    toggleViewButton.addEventListener('click', toggleView);

    if (addPatientForm){
        addPatientForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('patient-name').value;
            const severity = document.getElementById('severity').value;
            addPatient(name, severity);
        });
    }

    if (signinForm) {
        signinForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('patient-signin-name').value;
            const code = document.getElementById('patient-code').value;
            patientSignIn(name, code);
        });
    }

    updateQueue();
});

function toggleView() {
    isAdmin = !isAdmin;
    currentPatient = null; //clear the current patient when toggling views
    document.getElementById('admin-panel').classList.toggle('hidden');
    document.getElementById('patient-view').classList.toggle('hidden');
    renderQueue();
}

function renderQueue() {
    if (isAdmin) {
        renderAdminQueue();
    } else {
        renderPatientView();
    }
}

function renderPatientView() {
    const estimatedWaitTime = document.getElementById('estimated-wait-time');
    const queuePosition = document.getElementById('queue-position');
    
    if (currentPatient) {
        estimatedWaitTime.textContent = `${currentPatient.estimated_wait_time} minutes`;
        queuePosition.textContent = currentPatient.queue_position;
    } else {
        const waitingPatients = currentQueue.filter(p => p.status === 'Waiting');
        
        if (waitingPatients.length > 0) {
            const lastPatient = waitingPatients[waitingPatients.length - 1];
            estimatedWaitTime.textContent = `${lastPatient.estimated_wait_time} minutes`;
            queuePosition.textContent = waitingPatients.length;
        } else {
            estimatedWaitTime.textContent = 'No wait';
            queuePosition.textContent = 'No patients waiting';
        }
    }
}

function updateQueue()  {
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_queue'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentQueue = data.data.sort((a, b) => {
                if (a.severity !== b.severity) {
                    return a.severity - b.severity; // Sort by severity ascending (1 is highest priority)
                }
                return new Date(a.arrival_time) - new Date(b.arrival_time); // Then by arrival time
            });
            renderQueue();
        } else {
            console.error(`Error: ${data.message}`);
        }
    })
    .catch(error => console.error('Error:', error));
}

function patientSignIn(name, code) {
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=patient_signin&name=${encodeURIComponent(name)}&code=${encodeURIComponent(code)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentPatient = {
                name: name,
                code: code,
                estimated_wait_time: data.estimated_wait_time,
                queue_position: data.queue_position
            };
            renderPatientView();
            alert('Sign-in successful. Please check your estimated wait time and queue position.');
        } else {
            alert(`Sign-in failed: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during sign-in.');
    });
}


function addPatient(name, severity) {
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add_patient&name=${encodeURIComponent(name)}&severity=${encodeURIComponent(severity)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('add-patient-form').reset();
            alert(`Patient added successfully. Patient code: ${data.code}`);
            updateQueue();
        } else  {
            alert(`Error: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the patient.');
    });
}

function updateStatus(id, status) {
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&id=${encodeURIComponent(id)}&status=${encodeURIComponent(status)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateQueue();
        } else {
            alert(`Error: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status.');
    });
}


function renderAdminQueue() {
    const patientQueue = document.querySelector('#patient-queue tbody');
    patientQueue.innerHTML = '';
    currentQueue.forEach((patient) => {
        const row = patientQueue.insertRow();
        row.innerHTML = `
            <td>${patient.name}</td>
            <td>${patient.severity}</td>
            <td>${patient.estimated_wait_time !== null ? patient.estimated_wait_time + ' minutes' : 'In Treatment'}</td>
            <td>${patient.status}</td>
            <td>
                ${patient.status === 'Waiting' ? 
                    `<button onclick="updateStatus(${patient.id}, 'In Treatment')">Start Treatment</button>` : 
                    `<button onclick="updateStatus(${patient.id}, 'Discharged')">Discharge</button>`
                }
            </td>
        `;
    });
}



