# Kominiti Scheduling System API (Laravel)

This project is a RESTful API built with Laravel for managing events and participant registrations, developed as part of the Glimpse 33 Media PHP Laravel Backend Developer Task.

## Project Overview

The API allows organizers (potentially authenticated users, though auth is not implemented in this core version) to create events with details like name, start/end times, and participant limits. Participants can register for these events, with the system enforcing rules against exceeding maximum capacity and preventing participants from registering for overlapping events. [cite: 5, 6, 7]

## Key Features Implemented

* Event Creation (Name, Start/End Datetime, Max Participants, Image Upload)
* Event Listing & Details Retrieval
* Participant Registration for Events
* Validation:
    * Required fields for events and participants.
    * Event capacity limits enforced. [cite: 6]
    * Prevention of registration for overlapping events. [cite: 7]
    * Custom, user-friendly validation messages.
* Database Migrations & Seeding for sample data.
* API Resources for consistent JSON responses.
* Service Class (`RegistrationService`) for encapsulating business logic.


* **Assessment Extras:**
    * Asynchronous Notifications (using Queues & Log driver) for registration confirmation.
    * Basic Caching (for event listing).
    * API Rate Limiting (`throttle:api`).
    * Automated API Documentation (via Scribe).
    * Custom Logging Channel for notification errors.
    * Custom Artisan command (`app:install`) for streamlined setup.

## Technologies Used

* PHP (Specify Version, e.g., 8.1+)
* Laravel Framework (Specify Version, e.g., 11.x)
* MySQL (or specify your database)
* Composer (for dependency management)
* Laravel Sanctum (if API auth added, otherwise remove)
* Laravel Queues (Database or Redis driver)
* Laravel Caching (File or Database driver)
* Knuckleswtf/Scribe (for API documentation)
* PHPUnit (for testing)

## Requirements

* PHP >= 8.1 (Check your specific Laravel version requirements)
* Composer
* Database Server (e.g., MySQL, PostgreSQL)
* Web Server (Nginx or Apache - Optional, `php artisan serve` can be used)
* Redis (Optional, if using Redis for Cache/Queue)
* Node.js & NPM (Only if using frontend compilation, likely not needed for this API-only project)

## Installation & Setup

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/WisdomAyo/Scheduler scheduling-api
    cd scheduling-api
    ```

2.  **Install Dependencies:**
    ```bash
    composer install
    ```

3.  **Environment Setup:**
    * Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    * Open the `.env` file and configure your database connection details (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
    * Configure your `CACHE_DRIVER` and `QUEUE_CONNECTION` (e.g., `file`, `database`, `redis`).
    * Set `APP_URL` (e.g., `APP_URL=http://127.0.0.1:8000`).

4.  **Run  Application Install Command:**
    * This command handles key generation, API setup (Laravel 11+), database migrations, seeding (optional), storage linking, and cache clearing.
    ```bash
    php artisan app:install
    ```
    *(Follow the prompts during execution, e.g., confirming database wipe or seeding).*

5.  **Run Queue Worker (Required for Notifications):**
    * Open a new terminal window/tab in the project directory and run:
    ```bash
    php artisan queue:work
    ```
    * Keep this process running in the background to process jobs like notifications.

## Running the Application

* **Using Laravel's Development Server:**
    ```bash
    php artisan serve
    ```
    The API will typically be available at `http://127.0.0.1:8000`.

* **Using Docker (If Configured):**
    * *(Add specific Docker commands here if you set it up, e.g., `docker-compose up -d`)*

## API Documentation

API documentation is automatically generated using Scribe. Once the application is running (e.g., via `php artisan serve`), you can access the documentation in your browser at:

`http://<your-app-url>/docs` (e.g., `http://127.0.0.1:8000/docs`)

## API Endpoints Overview

*(Adjust based on your final routes)*

* **Events:**
    * `POST /api/v1/events`: Create a new event (requires `multipart/form-data` if including `event_image`).
    * `GET /api/v1/events`: List all events (paginated).
    * `GET /api/v1/events/{event}`: Get details of a specific event.
* **Registrations:**
    * `POST /api/v1/events/{event}/register`: Register a participant for an event (requires JSON body with `name` and `email`).
* **Participants:**
    * `GET /api/v1/participants/{participant}/registrations`: List events a specific participant is registered for.

*(Refer to the generated Scribe documentation for detailed request parameters, bodies, and example responses).*

## Running Tests

Execute the test suite using PHPUnit:

```bash
php artisan test
