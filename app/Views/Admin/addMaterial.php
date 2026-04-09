<?php
require_once __DIR__ . "/../../../core/auth.php";

mustBeStaff();

if (isDefaultPassword()) {
    header('Location: /../Auth/changePass.php');
    exit();
}

$role = $_SESSION['Role'];
$fname = $_SESSION['FullName'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/LIBRARYMANAGEMENTSYSTEMFINAL/">
    <script src="core/jq.js"></script>
    <title>Staff - Add Material</title>
</head>
<body>

    <h1>Library Management System</h1>

    <div id="navigation">
        <h2>Material Management</h2>
    </div>

    <div id="error_text" style="color:red; display:none;"></div>

    <button id="btnAddBook">Add Book</button>
    <button id="btnAddEbook">Add EBook</button>
    <button id="btnAddJournal">Add Journal</button>

    <div id="formBook">
        <h4>Add Book</h4>
        <input type="text" id="bookTitle" placeholder="Title"><br><br>
        <input type="text" id="bookAuthor" placeholder="Author"><br><br>
        <input type="text" id="bookISBN" placeholder="ISBN"><br><br>
        <textarea id="bookDesc" placeholder="Description"></textarea><br><br>
        <input type="text" id="bookGenre" placeholder="Genre"><br><br>
        <input type="date" id="bookPublishDate"><br><br>
        <input type="text" id="bookPublisher" placeholder="Publisher"><br><br>
        <label>Book Quantity</label>
        <input type="number" id="bookQty" value="1" min="1"><br><br>
        <label>Replacement Cost (₱)</label>
        <input type="number" id="bookReplacementCost" value="0.00" min="0" step="0.01"><br><br>
        <button onclick="addBook()">Save</button>
        <button onclick="hideAllForms()">Cancel</button>
    </div>

    <div id="formEbook">
        <h4>Add EBook</h4>
        <input type="text" id="ebookTitle" placeholder="Title"><br><br>
        <input type="text" id="ebookAuthor" placeholder="Author"><br><br>
        <input type="text" id="ebookISBN" placeholder="ISBN"><br><br>
        <textarea id="ebookDesc" placeholder="Description"></textarea><br><br>
        <input type="text" id="ebookGenre" placeholder="Genre"><br><br>
        <input type="date" id="ebookPublishDate"><br><br>
        <input type="text" id="ebookPublisher" placeholder="Publisher"><br><br>
        <select id="ebookFormat">
            <option value="EPUB">EPUB</option>
            <option value="PDF">PDF</option>
            <option value="AZW3">AZW3</option>
            <option value="MOBI">MOBI</option>
        </select><br><br>
        <label>Replacement Cost (₱)</label>
        <input type="number" id="ebookReplacementCost" value="0.00" min="0" step="0.01"><br><br>
        <button onclick="addEbook()">Save</button>
        <button onclick="hideAllForms()">Cancel</button>
    </div>

    <div id="formJournal">
        <h4>Add Journal</h4>
        <input type="text" id="journalTitle" placeholder="Title"><br><br>
        <input type="text" id="journalAuthor" placeholder="Author"><br><br>
        <input type="text" id="journalISBN" placeholder="ISSN (optional)"><br><br>
        <textarea id="journalDesc" placeholder="Description"></textarea><br><br>
        <input type="text" id="journalGenre" placeholder="Genre"><br><br>
        <input type="date" id="journalPublishDate"><br><br>
        <input type="text" id="journalPublisher" placeholder="Publisher"><br><br>
        <input type="number" id="journalQty" value="1" min="1"><br><br>
        <label>Type</label>
        <select id="journalType">
            <option value="Magazine">Magazine</option>
            <option value="Journal">Journal</option>
            <option value="Newspaper">Newspaper</option>
        </select><br><br>
        <label>Interval</label>
        <select id="journalInterval">
            <option value="Daily">Daily</option>
            <option value="Weekly">Weekly</option>
            <option value="Monthly">Monthly</option>
            <option value="Quarterly">Quarterly</option>
        </select><br><br>
        <label>Replacement Cost (₱)</label>
        <input type="number" id="journalReplacementCost" value="0.00" min="0" step="0.01"><br><br>
        <button onclick="addJournal()">Save</button>
        <button onclick="hideAllForms()">Cancel</button>
    </div>

    <div id="editForm">
        <h4>Edit Material</h4>
        <input type="hidden" id="editMaterialID">
        <input type="text" id="editTitle" placeholder="Title"><br><br>
        <input type="text" id="editAuthor" placeholder="Author"><br><br>
        <input type="text" id="editISBN" placeholder="ISBN"><br><br>
        <textarea id="editDesc" placeholder="Description"></textarea><br><br>
        <input type="text" id="editGenre" placeholder="Genre"><br><br>
        <input type="date" id="editPublishDate"><br><br>
        <input type="text" id="editPublisher" placeholder="Publisher"><br><br>
        <input type="number" id="editTotalQty" placeholder="Total Qty"><br><br>
        <input type="number" id="editAvailableQty" placeholder="Available Qty"><br><br>
        <label>Replacement Cost (₱)</label>
        <input type="number" id="editReplacementCost" value="0.00" min="0" step="0.01"><br><br>

        <div id="ebookFields">
            <label>EBook Format</label>
            <select id="editEbookFormat">
                <option value="">N/A</option>
                <option value="EPUB">EPUB</option>
                <option value="PDF">PDF</option>
                <option value="AZW3">AZW3</option>
                <option value="MOBI">MOBI</option>
            </select><br><br>
        </div>

        <div id="journalFields">
            <label>Journal Type</label>
            <select id="editJournalType">
                <option value="">N/A</option>
                <option value="Magazine">Magazine</option>
                <option value="Journal">Journal</option>
                <option value="Newspaper">Newspaper</option>
            </select><br><br>

            <label>Journal Interval</label>
            <select id="editJournalInterval">
                <option value="">N/A</option>
                <option value="Daily">Daily</option>
                <option value="Weekly">Weekly</option>
                <option value="Monthly">Monthly</option>
                <option value="Quarterly">Quarterly</option>
            </select><br><br>
        </div>

        <button onclick="updMaterial()">Update</button>
        <button onclick="hideAllForms()">Cancel</button>
    </div>

    <br>

    <input type="text" id="searchInput" placeholder="Search materials..." onkeyup="doMaterialSearch(this.value.trim())">

    <h2>Books</h2>
    <table id="materialTable" border="1">
        <thead>
            <tr>
                <th>ID</th><th>Type</th><th>Title</th><th>Author</th>
                <th>ISBN</th><th>Publisher</th><th>Genre</th>
                <th>Total Qty</th><th>Available Qty</th><th>Replacement Cost</th><th>Date Added</th><th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <h2>Ebooks</h2>
    <table id="ebookTable" border="1">
        <thead>
            <tr>
                <th>ID</th><th>Type</th><th>Title</th><th>Author</th>
                <th>ISBN</th><th>Publisher</th><th>Genre</th>
                <th>Replacement Cost</th><th>Date Added</th><th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <h2>Journals</h2>
    <table id="journalTable" border="1">
        <thead>
            <tr>
                <th>ID</th><th>Type</th><th>Title</th><th>Author</th>
                <th>ISBN</th><th>Publisher</th><th>Type</th>
                <th>Total Qty</th><th>Available Qty</th><th>Replacement Cost</th><th>Date Added</th><th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <br><br>

    <button onclick="location.href='app/Views/Dashboards/adminDashboard.php'">Go Back</button>

    <script src="app/Views/Admin/logMaterial.js?v=4"></script>

</body>
</html>