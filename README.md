# Team Tasker

**Team Tasker** is a Laravel-based Todo application designed to help teams manage tasks effectively. This application offers a straightforward and intuitive interface for task management, ideal for enhancing team collaboration and productivity.

## Features

- Create, update, and delete tasks.
- Assign tasks to team members.
- Set due dates and priorities.
- Track task progress.
- Responsive and user-friendly interface.

## Installation

To set up Team Tasker, follow these steps:

1. **Create the Project:**

   If you want to create a new instance of the project, use Composer to create it:

   ```bash
   composer create-project iamrohitpal/team-tasker
   ```

   Alternatively, if you have cloned the repository directly, proceed to install dependencies as shown in the next step.


2. **Configure the Environment:**

   Open the `.env` file and set up your environment configuration. For database setup, update the following entries:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=team_tasker
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password
   ```

3. **Create the Database:**

   Create a database named `team_tasker` on your MySQL server. You can do this using a MySQL client or via command line:

   ```sql
   CREATE DATABASE team_tasker;
   ```

4. **Run Database Migrations:**

   Set up the database schema by running migrations:

   ```bash
   php artisan migrate
   ```

5. **Start the Development Server:**

   Launch the Laravel development server:

   ```bash
   php artisan serve
   ```

   Open your web browser and visit `http://localhost:8000` to see the application in action.

## Usage

Once the application is set up, you can use it to:

- **Create Tasks:** Add tasks with titles, descriptions, and due dates.
- **Assign Tasks:** Assign tasks to team members and set priorities.
- **Track Progress:** Monitor the status and progress of tasks.


## Contributing

We welcome contributions to improve Team Tasker. To contribute:

1. Fork the repository.
2. Create a new branch (`git checkout -b feature-branch`).
3. Make your changes.
4. Commit your changes (`git commit -am 'Add new feature'`).
5. Push to your branch (`git push origin feature-branch`).
6. Open a pull request on GitHub.

## License

Team Tasker is licensed under the [MIT License](LICENSE).

## Contact

For any questions or issues, please reach out to:

- **Name:** Rohit Pal
- **Email:** [iamrohitpalg@gmail.com](mailto:iamrohitpalg@gmail.com)