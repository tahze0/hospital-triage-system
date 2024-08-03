# Hospital Triage System

A web application designed to help hospital staff manage patient queues and provide patients with estimated wait times. This system allows for efficient triage based on injury severity and waiting time.

This project is showcased in our [portfolio website](https://alperenakin.github.io/portfolio/).

## Technologies Used

- HTML, CSS for front-end design
- JavaScript for client-side interactions
- PHP for server-side interactions
- PostgreSQL (pgAdmin) for managing data

## How to Set Up and Run Locally

### Step 1: Setting up the Database

1. Clone this [repo](https://github.com/tahze0/hospital-triage-system)
2. Open pgAdmin. Create a new Database and name it "hospital_triage"
3. Right-click on the hospital_triage database. Select "Query Tool".
4. Load the schema.sql file found in the cloned repo. Click on the folder icon in the Query Tool toolbar to open a file dialog.
5. Select and open the schema.sql file from your local git repository.
6. Click execute script in the Query Tool toolbar to execute the script and import the schema required for the app.

### Step 2: Connect to DB and Run

1. Open the cloned repository directory in a text editor
2. Open the config.php file and update with your username and password (and port if needed).
3. Navigate to the repository folder from the command line/terminal.
4. Enter "php -S localhost:8000" to set up local server
5. Enter "localhost:8000" in your browser to start web app

## User Guide

### Admin Perspective

As an administrator (triage staff), you can:

1. Add new patients to the system
   - Enter the patient's name
   - Specify the severity of the patient's condition on a scale of 1 to 5 (1 being most severe)
2. View the full list of patients in the queue
3. Update patient status
   - Start treatment for a patient
   - Discharge (delete) a patient from the system

### Patient Perspective

As a patient, you can:

1. Sign in using the name and unique 3-letter code provided during registration
2. View your estimated wait time and position in the queue

## Technical Overview

The application follows a client-server architecture:
- Client-side JavaScript handles user interactions and updates the UI.
- Server-side PHP processes requests, interacts with the database, and returns JSON responses.
- PostgreSQL stores all patient and queue data.

The main table in our database is `patients` with fields for id, name, severity, code, status, and arrival time.

For more detailed technical information and to view the code, please visit our [GitHub repository](https://github.com/tahze0/hospital-triage-system).