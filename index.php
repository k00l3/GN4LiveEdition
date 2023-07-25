<!DOCTYPE html>
<html>
<head>
    <title>GN4 Live Editions</title>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-image: url('back.jpg');
            background-repeat: no-repeat;
            background-size: 50% auto;
            background-position: center;
            margin: 0;
            padding: 150px;
        }

        .container {
            max-width: 750px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
        }

        .container h1 {
            margin-top: 30px;
        }

        .container label, .container select, .container button {
            margin-right: 10px;
        }

        .square {
            float: left;
            position: relative;
            padding-bottom: 110px; /* = width for a 1:1 aspect ratio */
            margin: 5px;
            background-color: #FFFFFF;
            overflow: hidden;
            height: 122px;
            width: 122px;
        }

        .square:hover {
            opacity: 0.7;
            filter: alpha(opacity=10);
            height: 122px;
            width: 122px;
        }

        .square-content {
            position: absolute;
            height: 100%; /* = 100% - 2*5% padding */
            width: 100%; /* = 100% - 2*5% padding */
            padding: 5%;
            color: #FFFFFF;
            text-align: center;
            height: 122px;
            width: 122px;
            font-size: 10px;
        }

        .product-icon {
            font-size: 5em;
            font-weight: bold;
        }

        .product-description {
            font-family: 'Roboto', sans-serif;
            font-size: 15px;
        }

        .footer {
            position: relative;
            left: 0;
            bottom: 0;
            width: 100%;
            color: white;
            text-align: center;
            alignment: bottom;
        }

        .row {
            width: 100%;
            text-align: center;
        }

        .divTop {
            font-family: 'Roboto', sans-serif;
            font-size: 20px;
            font-weight: bold;
            color: #004D7D;
        }
    </style>
    <script type="text/javascript" src="jquery.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>GN4 Live Editions</h1>
        <label for="dates">Publication Date:</label>
        <select id="dates">
            <!-- The dropdown will be populated dynamically using JavaScript -->
        </select>

        <label for="editions"> Publication:</label>
        <select id="editions">
            <!-- The dropdown will be populated dynamically using JavaScript -->
        </select>

        <label for="descNames">Edition:</label>
        <select id="descNames">
            <!-- The dropdown will be populated dynamically using JavaScript -->
        </select>

        <button id="goButton">View</button>
    </div>

    <script>
        let descNamesDropdown;
        let editions;
        let ticket; // Define the ticket variable as a global variable

        // Function to format a date in 'DD-MM-YYYY' format
        function formatDate(dateString) {
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}-${month}-${year}`;
        }

        // Function to sort the dates dropdown in descending order
        function sortDatesDropdown() {
            const datesDropdown = document.getElementById('dates');
            const dateOptions = [...datesDropdown.options];
            dateOptions.sort((a, b) => {
                const dateA = new Date(a.value);
                const dateB = new Date(b.value);
                return dateB - dateA; // Sort in descending order
            });

            datesDropdown.innerHTML = '';
            dateOptions.forEach(option => {
                datesDropdown.appendChild(option);
            });
        }

        // Function to get tomorrow's date in 'YYYY-MM-DD' format
        function getTomorrowDate() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);

            const day = String(tomorrow.getDate()).padStart(2, '0');
            const month = String(tomorrow.getMonth() + 1).padStart(2, '0');
            const year = tomorrow.getFullYear();

            return `${year}-${month}-${day}`;
        }

        // Function to fetch results after logging in
        async function loginAndFetchResults() {
            const loginUrl = 'http://gn4.arena.africa/i4/do.ashx?cmd=login&name=Agfa_Service_Account&pwd=!AgfaAdmin1';
            const response = await fetch('proxy.php?url=' + encodeURIComponent(loginUrl));
            const loginData = await response.text();
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(loginData, 'text/xml');

            ticket = xmlDoc.querySelector('loginResult').getAttribute('ticket'); // Assign value to the global ticket variable

            const searchUrl = `http://gn4.arena.africa/i4/do.ashx?cmd=search&name=gn4:edition[@isTemplate='false'][gn4:titleRef/nav:refObject/gn4:title]&feed=resultList&ticket=${ticket}`;

            try {
                const searchResponse = await fetch('proxy.php?url=' + encodeURIComponent(searchUrl));
                const searchData = await searchResponse.text();
                const parser = new DOMParser();
                const searchXml = parser.parseFromString(searchData, 'text/xml');
                editions = searchXml.querySelectorAll('edition');

                const uniqueDates = new Set();
                const uniquePublications = new Set();

                editions.forEach(edition => {
                    const date = edition.getAttribute('date');
                    const publication = edition.getAttribute('publication');
                    uniqueDates.add(date);
                    uniquePublications.add(publication);
                });

                const datesDropdown = document.getElementById('dates');
                datesDropdown.innerHTML = '';
                uniqueDates.forEach(date => {
                    const option = document.createElement('option');
                    option.value = date;
                    option.text = formatDate(date);
                    datesDropdown.appendChild(option);
                });

                sortDatesDropdown(); // Sort the dates in descending order

                // Set tomorrow's date as the default selected option
                const tomorrowDate = getTomorrowDate();
                datesDropdown.value = tomorrowDate;

                const editionsDropdown = document.getElementById('editions');
                editionsDropdown.innerHTML = '';
                uniquePublications.forEach(publication => {
                    const option = document.createElement('option');
                    option.value = publication;
                    option.text = publication;
                    editionsDropdown.appendChild(option);
                });

                descNamesDropdown = document.getElementById('descNames');
                datesDropdown.addEventListener('change', applyFilters);
                editionsDropdown.addEventListener('change', applyFilters);

                applyFilters();
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }

        // Function to apply filters based on selected options
        function applyFilters() {
            const selectedDate = document.getElementById('dates').value;
            const selectedPublication = document.getElementById('editions').value;
            const filteredEditions = [...editions].filter(edition => {
                const date = edition.getAttribute('date');
                const publication = edition.getAttribute('publication');
                return date === selectedDate && publication === selectedPublication;
            });

            descNamesDropdown.innerHTML = '';
            filteredEditions.forEach(edition => {
                const descName = edition.getAttribute('descName');
                const option = document.createElement('option');
                option.value = descName;
                option.text = descName;
                option.dataset.ticket = edition.getAttribute('id'); // Store the ticket as a data attribute
                descNamesDropdown.appendChild(option);
            });

            sortDescNameDropdown();
        }

        // Function to sort the edition dropdown in descending order
        function sortDescNameDropdown() {
            const descNameOptions = descNamesDropdown.querySelectorAll('option');
            const sortedOptions = [...descNameOptions].sort((a, b) => {
                const dateA = new Date(a.getAttribute('date'));
                const dateB = new Date(b.getAttribute('date'));
                return dateB - dateA; // Sort in descending order
            });

            descNamesDropdown.innerHTML = '';
            sortedOptions.reverse().forEach(option => {
                descNamesDropdown.appendChild(option);
            });
        }

        // Function to fetch a new ticket
        async function getNewTicket() {
            const loginUrl = 'http://gn4.arena.africa/i4/do.ashx?cmd=login&name=Agfa_Service_Account&pwd=!AgfaAdmin1';
            const response = await fetch('proxy.php?url=' + encodeURIComponent(loginUrl));
            const loginData = await response.text();
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(loginData, 'text/xml');
            ticket = xmlDoc.querySelector('loginResult').getAttribute('ticket'); // Assign value to the global ticket variable
        }

        // Function to logout
        async function logout() {
            const logoutUrl = `http://gn4.arena.africa/i4/do.ashx?cmd=logout&ticket=${ticket}`;
            const response = await fetch('proxy.php?url=' + encodeURIComponent(logoutUrl));
            const data = await response.text();
            console.log('Logout response:', data);
        }

        // Function to open the selected edition in a new tab
        function openSelectedEdition() {
            const selectedDescName = descNamesDropdown.value;
            const selectedEdition = [...editions].find(edition => edition.getAttribute('descName') === selectedDescName);
            if (selectedEdition) {
                const id = selectedEdition.getAttribute('id');
                const goUrl = `https://gn4.arena.africa/i4/do.ashx?cmd=feed&name=editionInfoHtml&ids=${id}&ticket=${ticket}`;
                const newTab = window.open(goUrl, '_blank'); // Open in a new tab

                // Set the zoom level of the new tab to 80%
                newTab.onload = function() {
                    newTab.document.body.style.zoom = '80%';
                };

                // Logout after 3 seconds once the new tab is opened
                setTimeout(async () => {
                    await logout();
                }, 6000);
            } else {
                console.log('Selected DescName not found in editions.');
            }
        }

        // Function to handle the 'View' button click
        async function handleViewButtonClick() {
            await getNewTicket();
            openSelectedEdition();
        }

        // Function to automatically logout after 7 seconds
        function autoLogout() {
            setTimeout(logout, 30000);
        }

        // Call the loginAndFetchResults function when the page loads
        loginAndFetchResults();

        // Add event listener to 'View' button
        const goButton = document.getElementById('goButton');
        goButton.addEventListener('click', handleViewButtonClick);

        // Call the autoLogout function after 7 seconds
        autoLogout();
    </script>
</body>
</html>
