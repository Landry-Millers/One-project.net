
document.addEventListener('DOMContentLoaded', () => {
    const projectModal = document.getElementById("projectModal");
    const newProjectBtn = document.querySelector(".btn-new-project");
    const closeButtons = document.querySelectorAll(".close-button");
    const cancelProjectBtn = document.getElementById("cancelProjectBtn");
    const newProjectForm = document.getElementById("newProjectForm");

    const taskModal = document.getElementById("taskModal");
    const newTaskBtn = document.querySelector(".btn-new-task");
    const cancelTaskBtn = document.getElementById("cancelTaskBtn");
    const newTaskForm = document.getElementById("newTaskForm");

    const addMemberBtn = document.getElementById("addMemberBtn");
    const membersDropdown = document.getElementById("membersDropdown");
    const selectedMembersContainer = document.getElementById("selectedMembers");

    const projectListDiv = document.getElementById('projectList');
    const taskListUl = document.getElementById('taskList');
    const taskProjectSelect = document.getElementById('taskProject'); 
    const taskAssignedToSelect = document.getElementById('taskAssignedTo'); 

    let projects = JSON.parse(localStorage.getItem('projects')) || [
        { id: 'proj1', name: 'Refonte Site Web', description: 'Refonte complète du site web de l\'entreprise.', startDate: '2025-03-01', endDate: '2025-06-30', progress: 28, members: ['John Doe', 'Jane Smith'] },
        { id: 'proj2', name: 'Application Mobile', description: 'Développement d\'une nouvelle application mobile iOS et Android.', startDate: '2025-04-15', endDate: '2025-10-31', progress: 60, members: ['Peter Jones', 'Alice Johnson'] },
        { id: 'proj3', name: 'Optimisation SEO', description: 'Amélioration du référencement naturel pour augmenter le trafic.', startDate: '2025-05-01', endDate: '2025-07-31', progress: 90, members: ['John Doe'] }
    ];

    let tasks = JSON.parse(localStorage.getItem('tasks')) || [
        { id: 'task1', name: 'Développer page d\'accueil', description: 'Implémenter la nouvelle conception de la page d\'accueil.', dueDate: '2025-05-25', priority: 'Élevée', project: 'Refonte Site Web', assignedTo: 'John Doe', completed: false },
        { id: 'task2', name: 'Réunion client', description: 'Présentation des maquettes de l\'application.', dueDate: '2025-05-22', priority: 'Moyenne', project: 'Application Mobile', assignedTo: 'Jane Smith', completed: false },
        { id: 'task3', name: 'Rédiger contenu blog', description: 'Articles optimisés pour le SEO sur les nouvelles fonctionnalités.', dueDate: '2025-06-01', priority: 'Faible', project: 'Optimisation SEO', assignedTo: 'Alice Johnson', completed: false },
        { id: 'task4', name: 'Configurer la base de données', description: 'Mettre en place la structure de la base de données pour le site.', dueDate: '2025-05-20', priority: 'Élevée', project: 'Refonte Site Web', assignedTo: 'Peter Jones', completed: true }
    ];

    let members = [
        { id: 'mem1', name: 'John Doe', email: 'john.doe@example.com' },
        { id: 'mem2', name: 'Jane Smith', email: 'jane.smith@example.com' },
        { id: 'mem3', name: 'Peter Jones', email: 'peter.jones@example.com' },
        { id: 'mem4', name: 'Alice Johnson', email: 'alice.j@example.com' },
        { id: 'mem5', name: 'Bob Williams', email: 'bob.w@example.com' }
    ];

    let events = JSON.parse(localStorage.getItem('events')) || [
        { id: 'event1', date: '2025-05-20', time: '10:00', name: 'Daily Scrum', project: 'Refonte Site Web' },
        { id: 'event2', date: '2025-05-22', time: '14:30', name: 'Réunion Design', project: 'Application Mobile' },
        { id: 'event3', date: '2025-05-25', time: '09:00', name: 'Sprint Review', project: 'Refonte Site Web' }
    ];

    function saveToLocalStorage() {
        localStorage.setItem('projects', JSON.stringify(projects));
        localStorage.setItem('tasks', JSON.stringify(tasks));
        localStorage.setItem('events', JSON.stringify(events));
    }

    function generateUniqueId() {
        return '_' + Math.random().toString(36).substr(2, 9);
    }

    function openModal(modal) {
        modal.style.display = "flex";
        setTimeout(() => modal.classList.add('show'), 10); 
    }

    function closeModal(modal) {
        modal.classList.remove('show'); 
        setTimeout(() => modal.style.display = "none", 300); 
    }

    newProjectBtn.addEventListener('click', () => openModal(projectModal));
    newTaskBtn.addEventListener('click', () => {
        populateProjectSelect();
        openModal(taskModal);
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', (event) => closeModal(event.target.closest('.modal')));
    });

    cancelProjectBtn.addEventListener('click', () => closeModal(projectModal));
    cancelTaskBtn.addEventListener('click', () => closeModal(taskModal));

    window.addEventListener('click', (event) => {
        if (event.target === projectModal) {
            closeModal(projectModal);
        }
        if (event.target === taskModal) {
            closeModal(taskModal);
        }
    });

    function populateMembersDropdown() {
        membersDropdown.innerHTML = '';
        members.forEach(member => {
            const memberItem = document.createElement('label');
            memberItem.className = 'member-item';
            memberItem.innerHTML = `
                <input type="checkbox" value="${member.id}" data-name="${member.name}" data-email="${member.email}">
                <span class="member-name">${member.name}</span>
                <small>(${member.email})</small>
            `;
            membersDropdown.appendChild(memberItem);
        });

        membersDropdown.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedMembers);
        });
    }

    function updateSelectedMembers() {
        selectedMembersContainer.innerHTML = '';
        const selectedMemberIds = new Set();
        membersDropdown.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            const memberName = checkbox.dataset.name;
            const memberId = checkbox.value;
            selectedMemberIds.add(memberId);

            const tag = document.createElement('span');
            tag.className = 'selected-member-tag';
            tag.dataset.memberId = memberId; 
            tag.innerHTML = `
                ${memberName}
                <button type="button" class="remove-member">&times;</button>
            `;
            selectedMembersContainer.appendChild(tag);
        });
        newProjectForm.dataset.selectedMembers = JSON.stringify(Array.from(selectedMemberIds));

        selectedMembersContainer.querySelectorAll('.remove-member').forEach(button => {
            button.addEventListener('click', (event) => {
                const memberTag = event.target.closest('.selected-member-tag');
                const memberIdToRemove = memberTag.dataset.memberId;
                
                const checkboxToUncheck = membersDropdown.querySelector(`input[type="checkbox"][value="${memberIdToRemove}"]`);
                if (checkboxToUncheck) {
                    checkboxToUncheck.checked = false;
                }
                updateSelectedMembers(); 
            });
        });
    }

    addMemberBtn.addEventListener('click', () => {
        membersDropdown.style.display = membersDropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', (event) => {
        if (!membersDropdown.contains(event.target) && event.target !== addMemberBtn && !selectedMembersContainer.contains(event.target)) {
            membersDropdown.style.display = 'none';
        }
    });

    newProjectForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const projectName = document.getElementById('project-name').value;
        const projectDescription = document.getElementById('project-description').value;
        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;
        const selectedMemberIds = JSON.parse(newProjectForm.dataset.selectedMembers || '[]');

        const selectedMembersNames = members
            .filter(member => selectedMemberIds.includes(member.id))
            .map(member => member.name);

        const newProject = {
            id: generateUniqueId(),
            name: projectName,
            description: projectDescription,
            startDate: startDate,
            endDate: endDate,
            progress: 0, 
            members: selectedMembersNames
        };

        projects.push(newProject);
        saveToLocalStorage();
        renderProjects();
        populateProjectSelect(); 
        closeModal(projectModal);
        newProjectForm.reset(); 
        selectedMembersContainer.innerHTML = ''; 
        membersDropdown.style.display = 'none'; 
        membersDropdown.querySelectorAll('input[type="checkbox"]').forEach(checkbox => checkbox.checked = false); 
        alert('Nouveau projet créé avec succès !');
    });

    function renderProjects() {
        projectListDiv.innerHTML = '';
        projects.forEach(project => {
            const projectItem = document.createElement('div');
            projectItem.className = 'project-item';
            projectItem.innerHTML = `
                <h3>${project.name}</h3>
                <div class="progress-bar" style="--progress: ${project.progress}%">
                    <span>${project.progress}%</span>
                </div>
                <div class="project-meta">
                    <span><i class="fas fa-calendar-alt"></i> ${project.startDate} - ${project.endDate}</span>
                    <span><i class="fas fa-users"></i> ${project.members.length} membres</span>
                </div>
            `;
            projectListDiv.appendChild(projectItem);
        });
    }

    function populateProjectSelect() {
        taskProjectSelect.innerHTML = '<option value="">Sélectionner un projet</option>';
        projects.forEach(project => {
            const option = document.createElement('option');
            option.value = project.name;
            option.textContent = project.name;
            taskProjectSelect.appendChild(option);
        });
    }

    newTaskForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const taskName = document.getElementById('task-name').value;
        const taskDescription = document.getElementById('task-description').value;
        const taskDueDate = document.getElementById('task-due-date').value;
        const taskPriority = document.getElementById('task-priority').value;
        const taskProject = document.getElementById('taskProject').value;
        const taskAssignedTo = document.getElementById('taskAssignedTo').value;

        const newTask = {
            id: generateUniqueId(),
            name: taskName,
            description: taskDescription,
            dueDate: taskDueDate,
            priority: taskPriority,
            project: taskProject,
            assignedTo: taskAssignedTo,
            completed: false
        };

        tasks.push(newTask);
        saveToLocalStorage();
        renderTasks();
        closeModal(taskModal);
        newTaskForm.reset(); 
        alert('Nouvelle tâche créée avec succès !');
    });

    function renderTasks() {
        taskListUl.innerHTML = '';
        const sortedTasks = [...tasks].sort((a, b) => {
            if (a.completed !== b.completed) {
                return a.completed ? 1 : -1; 
            }
            return new Date(a.dueDate) - new Date(b.dueDate);
        });

        sortedTasks.forEach(task => {
            const taskItem = document.createElement('li');
            taskItem.className = `task-item ${task.completed ? 'completed' : ''}`;
            taskItem.dataset.taskId = task.id;
            taskItem.innerHTML = `
                <input type="checkbox" ${task.completed ? 'checked' : ''}>
                <div class="task-details">
                    <label class="${task.completed ? 'completed' : ''}">${task.name}</label>
                    <span class="task-project">${task.project}</span>
                    <span class="task-due-date"><i class="far fa-calendar-alt"></i> ${task.dueDate}</span>
                </div>
                <div class="task-actions">
                    <button class="btn-icon edit-task" title="Modifier"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon delete-task" title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                </div>
            `;
            taskListUl.appendChild(taskItem);
        });

        taskListUl.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const taskId = e.target.closest('.task-item').dataset.taskId;
                const task = tasks.find(t => t.id === taskId);
                if (task) {
                    task.completed = e.target.checked;
                    saveToLocalStorage();
                    renderTasks(); 
                }
            });
        });

        taskListUl.querySelectorAll('.delete-task').forEach(button => {
            button.addEventListener('click', (e) => {
                const taskId = e.target.closest('.task-item').dataset.taskId;
                if (confirm('Voulez-vous vraiment supprimer cette tâche ?')) {
                    tasks = tasks.filter(task => task.id !== taskId);
                    saveToLocalStorage();
                    renderTasks();
                }
            });
        });

        taskListUl.querySelectorAll('.edit-task').forEach(button => {
            button.addEventListener('click', (e) => {
                const taskId = e.target.closest('.task-item').dataset.taskId;
                const taskToEdit = tasks.find(t => t.id === taskId);
                if (taskToEdit) {
                    document.getElementById('task-name').value = taskToEdit.name;
                    document.getElementById('task-description').value = taskToEdit.description;
                    document.getElementById('task-due-date').value = taskToEdit.dueDate;
                    document.getElementById('task-priority').value = taskToEdit.priority;
                    document.getElementById('taskProject').value = taskToEdit.project;
                    document.getElementById('taskAssignedTo').value = taskToEdit.assignedTo;

                    document.getElementById('saveTaskBtn').textContent = 'Modifier la tâche';
                    document.getElementById('saveTaskBtn').dataset.editingTaskId = taskId;
                    document.getElementById('saveTaskBtn').innerHTML = '<i class="fas fa-save"></i> Modifier la tâche'; // Icon
                    
                    document.getElementById('cancelTaskBtn').style.display = 'inline-flex';

                    populateProjectSelect(); 
                    openModal(taskModal);
                }
            });
        });

        newTaskForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const saveTaskBtn = document.getElementById('saveTaskBtn');
            const editingTaskId = saveTaskBtn.dataset.editingTaskId;

            if (editingTaskId) {
                const taskIndex = tasks.findIndex(t => t.id === editingTaskId);
                if (taskIndex > -1) {
                    tasks[taskIndex].name = document.getElementById('task-name').value;
                    tasks[taskIndex].description = document.getElementById('task-description').value;
                    tasks[taskIndex].dueDate = document.getElementById('task-due-date').value;
                    tasks[taskIndex].priority = document.getElementById('task-priority').value;
                    tasks[taskIndex].project = document.getElementById('taskProject').value;
                    tasks[taskIndex].assignedTo = document.getElementById('taskAssignedTo').value;
                }
                delete saveTaskBtn.dataset.editingTaskId; 
                saveTaskBtn.textContent = 'Créer la tâche'; 
                saveTaskBtn.innerHTML = '<i class="fas fa-save"></i> Créer la tâche'; 

            } else {
                const taskName = document.getElementById('task-name').value;
                const taskDescription = document.getElementById('task-description').value;
                const taskDueDate = document.getElementById('task-due-date').value;
                const taskPriority = document.getElementById('task-priority').value;
                const taskProject = document.getElementById('taskProject').value;
                const taskAssignedTo = document.getElementById('taskAssignedTo').value;

                const newTask = {
                    id: generateUniqueId(),
                    name: taskName,
                    description: taskDescription,
                    dueDate: taskDueDate,
                    priority: taskPriority,
                    project: taskProject,
                    assignedTo: taskAssignedTo,
                    completed: false
                };
                tasks.push(newTask);
                alert('Nouvelle tâche créée avec succès !');
            }
            saveToLocalStorage();
            renderTasks();
            closeModal(taskModal);
            newTaskForm.reset(); 
        });
    }

    const monthNames = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
    const currentMonthYearHeader = document.getElementById('currentMonthYear');
    const calendarDatesDiv = document.getElementById('calendarDates');
    const prevMonthBtn = document.getElementById('prevMonthBtn');
    const nextMonthBtn = document.getElementById('nextMonthBtn');
    const selectedDateHeader = document.getElementById('selectedDate');
    const eventListDiv = document.getElementById('eventList');

    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let selectedCalendarDate = new Date().toISOString().split('T')[0]; 

    function renderCalendar() {
        calendarDatesDiv.innerHTML = '';
        currentMonthYearHeader.textContent = `${monthNames[currentMonth]} ${currentYear}`;

        const firstDayOfMonth = new Date(currentYear, currentMonth, 1).getDay(); 
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

        const startDayIndex = (firstDayOfMonth === 0) ? 6 : firstDayOfMonth - 1; 
        for (let i = 0; i < startDayIndex; i++) {
            calendarDatesDiv.innerHTML += '<span></span>';
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(currentYear, currentMonth, day);
            const dateString = date.toISOString().split('T')[0];
            const span = document.createElement('span');
            span.textContent = day;
            span.classList.add('current-month');

            if (dateString === new Date().toISOString().split('T')[0]) {
                span.classList.add('today');
            }
            if (dateString === selectedCalendarDate) {
                span.classList.add('selected-date');
            }
            if (events.some(event => event.date === dateString)) {
                span.classList.add('has-event');
            }

            span.dataset.date = dateString;
            span.addEventListener('click', () => {
                selectedCalendarDate = dateString;
                renderCalendar(); 
                renderEventsForSelectedDate();
            });
            calendarDatesDiv.appendChild(span);
        }

        renderEventsForSelectedDate(); 
    }

    function renderEventsForSelectedDate() {
        eventListDiv.innerHTML = '';
        selectedDateHeader.textContent = new Date(selectedCalendarDate).toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });

        const eventsForDate = events.filter(event => event.date === selectedCalendarDate).sort((a, b) => a.time.localeCompare(b.time));

        if (eventsForDate.length === 0) {
            eventListDiv.innerHTML = '<p style="text-align: center; color: #666;">Aucun événement pour cette date.</p>';
            return;
        }

        eventsForDate.forEach(event => {
            const eventItem = document.createElement('div');
            eventItem.className = 'event-item';
            eventItem.innerHTML = `
                <span class="event-time">${event.time}</span>
                <div class="event-details">
                    <p>${event.name}</p>
                    <small>Projet : ${event.project}</small>
                </div>
            `;
            eventListDiv.appendChild(eventItem);
        });
    }

    prevMonthBtn.onclick = () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar();
    };

    nextMonthBtn.onclick = () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar();
    };

    populateMembersDropdown(); 
    renderProjects(); 
    populateProjectSelect(); 
    renderTasks(); 
    renderCalendar(); 
});