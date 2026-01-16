<!DOCTYPE html>
<html>
<head>
    <title>System Bidding Project</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .card { border: 1px solid #ccc; padding: 15px; margin: 10px 0; border-radius: 6px; }
        .hidden { display: none; }
        select, input { margin: 5px 0; padding: 6px; }
    </style>
</head>
<body>

<h2>System Bidding Project</h2>

<!-- Sign In -->
<div id="loginSection" class="card">
    <h3>Sign In</h3>
    <input id="usernameInput" type="text" placeholder="Enter username" />
    <button onclick="login()">Login</button>
</div>

<!-- Main App -->
<div id="appSection" class="hidden">

    <h3>Welcome, <span id="currentUser"></span></h3>

    <!-- Create group -->
    <div class="card">
        <h3>Create Group</h3>
        <input id="groupNameInput" type="text" placeholder="Group name" />
        <button onclick="createGroup()">Create</button>
    </div>

    <!-- Groups List -->
    <div id="groupsList" class="card">
        <h3>Groups</h3>
        <div id="groupContainer"></div>
    </div>

</div>

<script>
    let currentUser = null;

    // Example in-memory database
    let groups = []; 
    /*
       groups = [
         { name: "Team Alpha",
           members: {
               admin: "alice",
               lead: "bob",
               approver: "chris",
               reviewer: "diana"
           }
         }
       ];
    */

    function login() {
        const username = document.getElementById("usernameInput").value.trim();
        if (!username) return alert("Enter a username!");

        currentUser = username;
        document.getElementById("currentUser").innerText = username;

        document.getElementById("loginSection").classList.add("hidden");
        document.getElementById("appSection").classList.remove("hidden");

        renderGroups();
    }

    function createGroup() {
        const name = document.getElementById("groupNameInput").value.trim();
        if (!name) return alert("Enter a group name!");

        groups.push({
            name,
            members: {
                admin: currentUser,      // creator = admin by default
                lead: "",
                approver: "",
                reviewer: ""
            }
        });

        document.getElementById("groupNameInput").value = "";
        renderGroups();
    }

    function updateRole(groupIndex, role, value) {
        groups[groupIndex].members[role] = value;
    }

    function renderGroups() {
        const container = document.getElementById("groupContainer");
        container.innerHTML = "";

        groups.forEach((group, index) => {
            const div = document.createElement("div");
            div.className = "card";

            div.innerHTML = `
                <h4>${group.name}</h4>

                <label>Admin</label><br>
                <input type="text" value="${group.members.admin}" 
                       oninput="updateRole(${index}, 'admin', this.value)" /><br>

                <label>Project Lead</label><br>
                <input type="text" value="${group.members.lead}" 
                       oninput="updateRole(${index}, 'lead', this.value)" /><br>

                <label>Executive Approver</label><br>
                <input type="text" value="${group.members.approver}" 
                       oninput="updateRole(${index}, 'approver', this.value)" /><br>

                <label>Reviewer</label><br>
                <input type="text" value="${group.members.reviewer}" 
                       oninput="updateRole(${index}, 'reviewer', this.value)" />
            `;

            container.appendChild(div);
        });
    }
</script>

</body>
</html>
