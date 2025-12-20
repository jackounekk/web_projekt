function searchSection(e) {
    if (e.key === "Enter") { // spustí se při Enter
        let value = document.getElementById("sectionSearch").value.toLowerCase();

        if (value.includes("dom")) window.location.hash = "#domu";
        else if (value.includes("kur")) window.location.hash = "#kurzy";
        else if (value.includes("lek")) window.location.hash = "#lektori";
        else if (value.includes("stu")) window.location.hash = "#studenti";
        else alert("Sekce nenalezena");
    }
}

// Add-row handlers (non-persistent, client-side only)
function addCourse(e){
    e.preventDefault();
    const name = document.getElementById('course-name').value.trim();
    const date = document.getElementById('course-date').value;
    if(!name){ alert('Zadejte název kurzu.'); return; }
    const tbody = document.querySelector('#courses table tbody');
    const tr = document.createElement('tr');
    const tdName = document.createElement('td'); tdName.textContent = name;
    const tdDate = document.createElement('td'); tdDate.textContent = date || '';
    tr.appendChild(tdName); tr.appendChild(tdDate);
    tbody.appendChild(tr);
    document.getElementById('form-add-course').reset();
}

function addInstructor(e){
    e.preventDefault();
    const name = document.getElementById('instructor-name').value.trim();
    const spec = document.getElementById('instructor-specialty').value.trim();
    if(!name){ alert('Zadejte jméno lektora.'); return; }
    const tbody = document.querySelector('#instructors table tbody');
    const tr = document.createElement('tr');
    const tdName = document.createElement('td'); tdName.textContent = name;
    const tdSpec = document.createElement('td'); tdSpec.textContent = spec;
    tr.appendChild(tdName); tr.appendChild(tdSpec);
    tbody.appendChild(tr);
    document.getElementById('form-add-instructor').reset();
}

function addStudent(e){
    e.preventDefault();
    const name = document.getElementById('student-name').value.trim();
    const course = document.getElementById('student-course').value.trim();
    if(!name){ alert('Zadejte jméno studenta.'); return; }
    const tbody = document.querySelector('#students table tbody');
    const tr = document.createElement('tr');
    const tdName = document.createElement('td'); tdName.textContent = name;
    const tdCourse = document.createElement('td'); tdCourse.textContent = course;
    tr.appendChild(tdName); tr.appendChild(tdCourse);
    tbody.appendChild(tr);
    document.getElementById('form-add-student').reset();
}

// Optional: prevent form submission when JS unavailable; keep handlers callable from inline onsubmit
document.addEventListener('DOMContentLoaded', ()=>{
    // ensure forms work if someone prefers addEventListener pattern
    const f1 = document.getElementById('form-add-course'); if(f1) f1.addEventListener('submit', addCourse);
    const f2 = document.getElementById('form-add-instructor'); if(f2) f2.addEventListener('submit', addInstructor);
    const f3 = document.getElementById('form-add-student'); if(f3) f3.addEventListener('submit', addStudent);
});