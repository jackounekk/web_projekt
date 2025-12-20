 function login() {
            const u = document.getElementById("username").value;
            const p = document.getElementById("password").value;

            // demo kontrola
            if (u === "admin" && p === "1234") {
                // přesměrování na hlavní stránku
                window.location.href = "home.html";
            } else {
                alert("Špatné přihlašovací údaje!");
            }
        }
    