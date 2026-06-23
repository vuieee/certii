// Mock Database
const dbMock = {
    enrollments: [
        { id: 1, title: "Cybersecurity Basics", status: "In Progress" },
        { id: 2, title: "Workplace Ethics", status: "Not Started" }
    ],
    warnings: [
        { title: "Advanced Management", exp: "2026-07-01", status: "Nearing Expiration", class: "bg-warn" },
        { title: "Data Privacy L1", exp: "2025-12-01", status: "Expired", class: "bg-danger" }
    ],
    certs: [
        { id: 301, title: "Leadership 101", issue: "2024-01-15", exp: "2027-01-15", status: "Valid", class: "bg-valid" },
        { id: 302, title: "Advanced Management", issue: "2023-07-01", exp: "2026-07-01", status: "Nearing Expiration", class: "bg-warn" },
        { id: 303, title: "Data Privacy L1", issue: "2023-12-01", exp: "2025-12-01", status: "Expired", class: "bg-danger" },
        { id: 304, title: "OSHA Compliance", issue: "2025-01-10", exp: "2028-01-10", status: "Valid", class: "bg-valid" }
    ],
    courses: [
        { id: 201, title: "Cybersecurity Basics", cat: "Compliance" },
        { id: 202, title: "Advanced Management", cat: "Leadership" },
        { id: 203, title: "Workplace Ethics", cat: "HR" },
        { id: 204, title: "OSHA Compliance", cat: "Safety" },
        { id: 205, title: "Conflict Resolution", cat: "HR" },
        { id: 206, title: "Cloud Architecture", cat: "IT" }
    ]
};

const loginScreen = document.getElementById('login-screen');
const loadingScreen = document.getElementById('loading-screen');
const mainContent = document.getElementById('main-content');

function attemptLogin() {
    const inputId = document.getElementById('login-id').value.trim();
    if (inputId === "1001") {
        document.getElementById('login-error').style.display = 'none';
        startBootSequence(inputId);
    } else {
        document.getElementById('login-error').style.display = 'block';
    }
}

document.getElementById('btn-login').addEventListener('click', attemptLogin);

document.getElementById('login-id').addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        attemptLogin();
    }
});

document.getElementById('btn-logout').addEventListener('click', () => {
    mainContent.classList.remove('active');
    document.getElementById('login-id').value = '';
    setTimeout(() => {
        loginScreen.classList.add('active');
        switchTab('nav-dash', 'view-dash'); // Reset to dashboard
    }, 500);
});

function startBootSequence(empId) {
    loginScreen.classList.remove('active');
    
    setTimeout(() => {
        loadingScreen.classList.add('active');
        
        let progress = 0;
        const barElement = document.getElementById('ascii-bar');
        const textElement = document.getElementById('ascii-text');
        
        const loadInterval = setInterval(() => {
            progress += 2;
            
            const filled = Math.floor(progress / 5);
            const empty = 20 - filled;
            barElement.innerText = `[${'█'.repeat(filled)}${'.'.repeat(empty)}] ${progress}%`;

            if(progress === 50) textElement.innerText = "RETRIEVING EMPLOYEE RECORDS...";
            if(progress === 80) textElement.innerText = "DECRYPTING CERTIFICATIONS...";

            if (progress >= 100) {
                clearInterval(loadInterval);
                textElement.innerText = "ACCESS GRANTED.";
                
                setTimeout(() => {
                    loadingScreen.classList.remove('active');
                    initDashboard(empId);
                    setTimeout(() => { mainContent.classList.add('active'); }, 400);
                }, 500);
            }
        }, 30); 
    }, 400);
}

function initDashboard(empId) {
    document.getElementById('header-sys-id').innerText = `SYSTEM ID: ${empId}`;

    const enrollBox = document.getElementById('enrollments-list');
    enrollBox.innerHTML = '';
    dbMock.enrollments.forEach(e => {
        enrollBox.innerHTML += `
            <div class="list-item">
                <span>${e.title}</span>
                <span style="font-size:0.8rem; color:#72767d;">${e.status}</span>
            </div>`;
    });

    const warnBox = document.getElementById('compliance-list');
    warnBox.innerHTML = '';
    dbMock.warnings.forEach(w => {
        warnBox.innerHTML += `
            <div class="list-item">
                <span>${w.title}</span>
                <span class="status ${w.class}">${w.status}</span>
            </div>`;
    });

    const certBox = document.getElementById('recent-certs');
    certBox.innerHTML = '';
    dbMock.certs.slice(0, 4).forEach(c => {
        certBox.innerHTML += `
            <div class="cert-card">
                <div class="cert-thumb">[ DOCUMENT THUMBNAIL ]</div>
                <div style="font-weight:bold; font-size:0.85rem; margin-bottom:5px;">${c.title}</div>
                <div style="font-size:0.75rem; color:#72767d; margin-bottom:10px;">Exp: ${c.exp}</div>
                <div><span class="status ${c.class}">${c.status}</span></div>
            </div>`;
    });
}

function switchTab(navId, viewId) {

    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    document.getElementById(navId).classList.add('active');

    document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
    document.getElementById(viewId).classList.add('active');
}

document.getElementById('nav-dash').addEventListener('click', () => {
    switchTab('nav-dash', 'view-dash');
});

document.getElementById('nav-catalog').addEventListener('click', () => {
    switchTab('nav-catalog', 'view-catalog');
    const content = document.getElementById('courses-content');
    content.innerHTML = '';
    dbMock.courses.forEach(c => {
        content.innerHTML += `
            <div class="cert-card">
                <div class="cert-thumb">COURSE MATERIAL</div>
                <div style="font-weight:bold;">${c.title}</div>
                <div style="font-size:0.8rem; color:#72767d; margin-bottom:1vh;">Category: ${c.cat}</div>
                <button style="margin-top:auto; padding:8px; border:1px solid #99aab5; background:white; font-weight:bold; cursor:pointer;">ENROLL NOW</button>
            </div>`;
    });
});

document.getElementById('nav-certs').addEventListener('click', () => {
    switchTab('nav-certs', 'view-certs');
    const content = document.getElementById('certs-content');
    content.innerHTML = '';
    dbMock.certs.forEach(c => {
        content.innerHTML += `
            <div class="cert-card">
                <div class="cert-thumb">CERTIFICATE PDF</div>
                <div style="font-weight:bold; margin-bottom:5px;">${c.title}</div>
                <div style="font-size:0.8rem;">Issue: ${c.issue}</div>
                <div style="font-size:0.8rem; margin-bottom:10px;">Exp: ${c.exp}</div>
                <div><span class="status ${c.class}">${c.status}</span></div>
            </div>`;
    });
});

const mobileToggle = document.getElementById('mobile-nav-toggle');
const mainNav = document.getElementById('main-nav');
mobileToggle.addEventListener('click', () => mainNav.classList.toggle('open'));