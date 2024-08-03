# Hospital Triage System

Web App implemented using:

- HTML, CSS --> Front-end Design
- Javascript --> Client Side Interactions
- PHP --> Server Side Interactions
- Postgres (pgAdmin) DBMS --> Managing Data

This project Repo is linked in our [portfolio website](https://alperenakin.github.io/portfolio/).

## How to Set Up/ Run Locally

#### Step 1: Setting up the Database
1. Clone this [repo](https://github.com/tahze0/hospital-triage-system)
2. Open pgAdmin. Create a new Database and name it "hotel_triage"
3. Right-click on the hospital_triage database. Select "Query Tool".
4. Load the schema.sql file found in the cloned repo. Click on the folder icon in the Query Tool toolbar to open a file dialog.
5. Select and open the schema.sql file from your local git repository.
6. Click execute script in the Query Tool toolbar to execute the script and import the schema required for the app.

#### Step 2: Connect to db and run
1. Open the cloned repository directory in a text editor
2. Open the config.php file and update with your username and password (and port if needed).
3. Navigate to the repository folder from the command line/terminal.
4. Enter "php -S localhost:8000" to set up local server
5. Enter "localhost:8000" in your browser to start web app

## How does the web app work?

There are 2 users: patient and admin. 

#### Admin
- can add a patient using their name
- specifies the severity of the patient's condition on a scale of 1 to 5
- Able to modify the satus of a patient by starting treatment and discharging(delete a patient).

#### Patient
- once admin adds a patient, a code is generated that the patient will use to sign in
- once signed in, patient can view their status and wait time.








