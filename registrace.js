/* function addCourse(e){
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
} */