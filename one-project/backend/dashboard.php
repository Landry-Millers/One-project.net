<?php
// pages/dashboard.php
session_start();

// Vérifier si l'utilisateur est connecté, sinon rediriger vers la page de connexion
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.html");
    exit;
}

require_once '../backend/config.php';

$user_id = $_SESSION["id"];
$user_full_name = $_SESSION["full_name"]; // Récupérer le nom complet de la session

// Récupérer les projets de l'utilisateur
$projects = [];
$sql_projects = "SELECT id, project_name, description, status, start_date, end_date FROM projects WHERE user_id = ? ORDER BY created_at DESC";
if ($stmt_projects = mysqli_prepare($link, $sql_projects)) {
    mysqli_stmt_bind_param($stmt_projects, "i", $user_id);
    if (mysqli_stmt_execute($stmt_projects)) {
        $result_projects = mysqli_stmt_get_result($stmt_projects);
        while ($row = mysqli_fetch_assoc($result_projects)) {
            $projects[] = $row;
        }
    }
    mysqli_stmt_close($stmt_projects);
}

// Récupérer les tâches de l'utilisateur (on suppose qu'une tâche est liée à un projet de l'utilisateur)
// C'est un peu plus complexe car on doit joindre avec la table projects
$tasks = [];
$sql_tasks = "SELECT t.id, t.task_name, t.description, t.due_date, t.assigned_to, t.status, p.project_name
              FROM tasks t JOIN projects p ON t.project_id = p.id
              WHERE p.user_id = ? ORDER BY t.due_date ASC";
if ($stmt_tasks = mysqli_prepare($link, $sql_tasks)) {
    mysqli_stmt_bind_param($stmt_tasks, "i", $user_id);
    if (mysqli_stmt_execute($stmt_tasks)) {
        $result_tasks = mysqli_stmt_get_result($stmt_tasks);
        while ($row = mysqli_fetch_assoc($result_tasks)) {
            $tasks[] = $row;
        }
    }
    mysqli_stmt_close($stmt_tasks);
}

// Récupérer tous les utilisateurs (pour le champ "Assigné à" dans les tâches)
$all_users = [];
$sql_all_users = "SELECT id, full_name FROM users ORDER BY full_name ASC";
if ($result_all_users = mysqli_query($link, $sql_all_users)) {
    while ($row = mysqli_fetch_assoc($result_all_users)) {
        $all_users[] = $row;
    }
    mysqli_free_result($result_all_users);
}


// Récupérer les événements (pour le calendrier)
// Pour l'exemple, nous allons simuler des événements basés sur les tâches avec des dates d'échéance.
// Dans un système réel, vous auriez une table 'events' ou 'calendar_events'.
$events = [];
foreach ($tasks as $task) {
    if (!empty($task['due_date'])) {
        $events[] = [
            'date' => $task['due_date'],
            'time' => 'Tâche', // Ou une heure si la tâche a une heure
            'name' => $task['task_name'],
            'project' => $task['project_name']
        ];
    }
}
// Optionnel: ajouter un événement pour le démarrage du projet (exemple)
foreach ($projects as $project) {
    if (!empty($project['start_date'])) {
        $events[] = [
            'date' => $project['start_date'],
            'time' => 'Projet',
            'name' => $project['project_name'],
            'project' => 'Démarrage du projet'
        ];
    }
}
// Trier les événements par date et heure
usort($events, function($a, $b) {
    return strtotime($a['date'] . ' ' . $a['time']) - strtotime($b['date'] . ' ' . $b['time']);
});

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - One Project</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <header>
        <nav class="navbar dashboard-nav">
            <div class="logo">One Project</div>
            <div class="nav-right">
                <div class="search-bar-container">
                    <input type="text" id="globalSearch" placeholder="Rechercher projets, tâches, membres..." class="global-search-input">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <div class="user-menu">
                    <img src="../assets/images/avatar.jpg" alt="Avatar" class="user-avatar">
                    <span><?php echo htmlspecialchars($user_full_name); ?></span> <i class="fas fa-chevron-down"></i>
                    <div class="dropdown-menu">
                        <a href="#"><i class="fas fa-user"></i> Profil</a>
                        <a href="#"><i class="fas fa-cog"></i> Paramètres</a>
                        <a href="../backend/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a> </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="dashboard-content">
        <section class="welcome-banner">
            <h1>Bonjour, <?php echo htmlspecialchars($user_full_name); ?>!</h1>
            <p>Bienvenue sur votre tableau de bord. Ici, vous pouvez gérer tous vos projets et tâches.</p>
        </section>

        <section class="dashboard-overview">
            <div class="widget card-projects">
                <h2><i class="fas fa-rocket"></i> Vos Projets</h2>
                <div class="project-list" id="projectList">
                    <?php if (empty($projects)): ?>
                        <p class="no-data">Aucun projet pour l'instant. Créez-en un !</p>
                    <?php else: ?>
                        <?php foreach ($projects as $project): ?>
                            <div class="project-item" data-project-id="<?php echo $project['id']; ?>">
                                <h3><?php echo htmlspecialchars($project['project_name']); ?></h3>
                                <div class="project-meta">
                                    <span>Statut: <?php echo htmlspecialchars($project['status']); ?></span>
                                    <span>Début: <?php echo htmlspecialchars($project['start_date']); ?></span>
                                    <span>Fin: <?php echo htmlspecialchars($project['end_date']); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars(substr($project['description'], 0, 100)); ?>...</p>
                                <div class="project-actions">
                                    <button class="btn-sm btn-view"><i class="fas fa-eye"></i> Voir</button>
                                    <button class="btn-sm btn-edit"><i class="fas fa-edit"></i> Modifier</button>
                                    <button class="btn-sm btn-delete"><i class="fas fa-trash-alt"></i> Supprimer</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button class="btn-secondary btn-new-project" id="newProjectBtn"><i class="fas fa-plus"></i> Nouveau Projet</button>
            </div>

            <div class="widget card-tasks">
                <h2><i class="fas fa-tasks"></i> Vos Tâches</h2>
                <ul class="task-list" id="taskList">
                    <?php if (empty($tasks)): ?>
                        <p class="no-data">Aucune tâche pour l'instant. Ajoutez-en une !</p>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <li class="task-item" data-task-id="<?php echo $task['id']; ?>">
                                <label>
                                    <input type="checkbox" <?php echo ($task['status'] === 'Terminée') ? 'checked' : ''; ?>>
                                    <span class="task-name"><?php echo htmlspecialchars($task['task_name']); ?></span>
                                </label>
                                <div class="task-meta">
                                    <span class="task-project"><?php echo htmlspecialchars($task['project_name']); ?></span>
                                    <span class="task-due-date"><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($task['due_date']); ?></span>
                                    <span class="task-assigned-to"><i class="fas fa-user"></i> <?php echo htmlspecialchars($task['assigned_to']); ?></span>
                                </div>
                                <div class="task-actions">
                                    <button class="btn-sm btn-edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn-sm btn-delete"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <button class="btn-secondary btn-new-task" id="newTaskBtn"><i class="fas fa-plus"></i> Nouvelle Tâche</button>
            </div>
            
            <div class="widget card-calendar">
                <h2><i class="fas fa-calendar-alt"></i> Calendrier</h2>
                <div class="calendar-header">
                    <button id="prevMonthBtn"><i class="fas fa-chevron-left"></i></button>
                    <span id="currentMonthYear"></span>
                    <button id="nextMonthBtn"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="calendar-grid">
                    <div class="calendar-weekdays">
                        <span>Dim</span><span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span>
                    </div>
                    <div class="calendar-dates" id="calendarDates">
                        </div>
                </div>
                <div class="calendar-events" id="eventList">
                    <h3>Événements du jour: <span id="selectedDate"></span></h3>
                    </div>
            </div>
        </section>
    </main>

    <div id="projectModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2><i class="fas fa-rocket"></i> Créer un nouveau projet</h2>
            <form id="newProjectForm" class="project-form" action="../backend/add_project.php" method="POST">
                <div class="input-group">
                    <label for="projectName">Nom du projet</label>
                    <input type="text" id="projectName" name="project_name" placeholder="Ex: Refonte site web" required>
                </div>
                <div class="input-group">
                    <label for="projectDescription">Description</label>
                    <textarea id="projectDescription" name="description" placeholder="Décrivez votre projet..."></textarea>
                </div>
                <div class="input-group">
                    <label for="projectStartDate">Date de début</label>
                    <input type="date" id="projectStartDate" name="start_date">
                </div>
                <div class="input-group">
                    <label for="projectEndDate">Date de fin</label>
                    <input type="date" id="projectEndDate" name="end_date">
                </div>
                <div class="input-group">
                    <label for="projectStatus">Statut</label>
                    <select id="projectStatus" name="status">
                        <option value="En cours">En cours</option>
                        <option value="Terminé">Terminé</option>
                        <option value="En attente">En attente</option>
                        <option value="Annulé">Annulé</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelProjectBtn"><i class="fas fa-times"></i> Annuler</button>
                    <button type="submit" class="btn-submit" id="saveProjectBtn"><i class="fas fa-save"></i> Créer le projet</button>
                </div>
            </form>
        </div>
    </div>

    <div id="taskModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2><i class="fas fa-tasks"></i> Créer une nouvelle tâche</h2>
            <form id="newTaskForm" class="task-form" action="../backend/add_task.php" method="POST">
                <div class="input-group">
                    <label for="taskName">Nom de la tâche</label>
                    <input type="text" id="taskName" name="task_name" placeholder="Ex: Réunion d'équipe" required>
                </div>
                <div class="input-group">
                    <label for="taskDescription">Description</label>
                    <textarea id="taskDescription" name="description" placeholder="Détails de la tâche..."></textarea>
                </div>
                <div class="input-group">
                    <label for="taskDueDate">Date d'échéance</label>
                    <input type="date" id="taskDueDate" name="due_date">
                </div>
                <div class="input-group">
                    <label for="taskProject">Projet</label>
                    <select id="taskProject" name="project_id" required>
                        <option value="">Sélectionner un projet</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['project_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="taskAssignedTo">Assigné à</label>
                    <select id="taskAssignedTo" name="assigned_to">
                        <option value="">Sélectionner un membre</option>
                        <?php foreach ($all_users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['full_name']); ?>"><?php echo htmlspecialchars($user['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="taskStatus">Statut</label>
                    <select id="taskStatus" name="status">
                        <option value="À faire">À faire</option>
                        <option value="En cours">En cours</option>
                        <option value="Terminée">Terminée</option>
                        <option value="Bloquée">Bloquée</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelTaskBtn"><i class="fas fa-times"></i> Annuler</button>
                    <button type="submit" class="btn-submit" id="saveTaskBtn"><i class="fas fa-save"></i> Créer la tâche</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>One Project</h3>
                <p>Simplifiez la gestion de vos projets.</p>
            </div>
            <div class="footer-section">
                <h3>Liens rapides</h3>
                <ul>
                    <li><a href="index.html">Accueil</a></li>
                    <li><a href="about.html">À propos</a></li>
                    <li><a href="contact.html">Contactez-nous</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>One-project@gmail.com</p>
                <p>+261 34 68 775 68</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 One Project. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="../js/script.js"></script>
    <script>
        // Passer les données PHP aux scripts JS (pour le calendrier, par exemple)
        const projectsData = <?php echo json_encode($projects); ?>;
        const tasksData = <?php echo json_encode($tasks); ?>;
        const allUsersData = <?php echo json_encode($all_users); ?>;
        const eventsData = <?php echo json_encode($events); ?>; // Passer les événements au JS pour le calendrier
        
        // La fonction `renderCalendar` et les autres fonctions JavaScript dans script.js
        // devront être ajustées pour utiliser `eventsData` et `allUsersData`
        // au lieu de données simulées.
        
        // Pour `populateMembersDropdown()` et `taskProjectSelect` dans script.js,
        // vous devrez peut-être les adapter pour utiliser les données PHP directement,
        // ou simplement vous assurer que les `select` HTML sont bien remplis par PHP.

        // Initialisation du calendrier après que les données soient disponibles
        document.addEventListener('DOMContentLoaded', () => {
            renderCalendar(eventsData); // Passer les données d'événements au calendrier
            // Assurez-vous que populateMembersDropdown() et autres fonctions JS
            // qui dépendent de données dynamiques utilisent `allUsersData` et `projectsData`.
            // Ou, si les éléments <select> sont déjà remplis par PHP, ces fonctions JS n'ont qu'à gérer les interactions.
        });
    </script>

</body>
</html>