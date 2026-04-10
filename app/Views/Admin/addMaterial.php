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
    <link rel="stylesheet" href="app/Views/CSS/generalize.css">
    <title>Staff - Material Management</title>
</head>
<body>

<header>
    <h1>Nyle's Library Management System</h1>
    <div class="header-right">
        <span><?php echo $role; ?> — <?php echo htmlspecialchars($fname); ?></span>
        <a class="btn btn-ghost" href="app/Views/Dashboards/adminDashboard.php">← Dashboard</a>
        <button class="btn-logout" onclick="logout()">Logout</button>
    </div>
</header>

<div class="subbar"><span>Material Management</span></div>

<!-- modal overlay -->
<div id="modalOverlay">
    <div id="modalBox">
        <div id="error_text"></div>

        <div id="formBook" class="form-box">
            <h3>Add Book</h3>
            <div class="form-row"><label>Title *</label><input type="text" id="bookTitle" placeholder="Title"></div>
            <div class="form-row"><label>Author *</label><input type="text" id="bookAuthor" placeholder="Author"></div>
            <div class="form-row"><label>ISBN</label><input type="text" id="bookISBN" placeholder="ISBN"></div>
            <div class="form-row"><label>Description</label><textarea id="bookDesc" placeholder="Description"></textarea></div>
            <div class="form-row"><label>Genre</label><input type="text" id="bookGenre" placeholder="Genre"></div>
            <div class="form-row"><label>Publish Date</label><input type="date" id="bookPublishDate"></div>
            <div class="form-row"><label>Publisher *</label><input type="text" id="bookPublisher" placeholder="Publisher"></div>
            <div class="form-row"><label>Quantity</label><input type="number" id="bookQty" value="1" min="1"></div>
            <div class="form-row"><label>Replacement Cost (₱)</label><input type="number" id="bookReplacementCost" value="0.00" min="0" step="0.01"></div>
            <div class="form-actions">
                <button class="btn-primary" onclick="addBook()">Save</button>
                <button class="cancel-btn" onclick="hideAllForms()">Cancel</button>
            </div>
        </div>

        <div id="formEbook" class="form-box">
            <h3>Add EBook</h3>
            <div class="form-row"><label>Title *</label><input type="text" id="ebookTitle" placeholder="Title"></div>
            <div class="form-row"><label>Author *</label><input type="text" id="ebookAuthor" placeholder="Author"></div>
            <div class="form-row"><label>ISBN</label><input type="text" id="ebookISBN" placeholder="ISBN"></div>
            <div class="form-row"><label>Description</label><textarea id="ebookDesc" placeholder="Description"></textarea></div>
            <div class="form-row"><label>Genre</label><input type="text" id="ebookGenre" placeholder="Genre"></div>
            <div class="form-row"><label>Publish Date</label><input type="date" id="ebookPublishDate"></div>
            <div class="form-row"><label>Publisher *</label><input type="text" id="ebookPublisher" placeholder="Publisher"></div>
            <div class="form-row"><label>Format</label>
                <select id="ebookFormat">
                    <option value="EPUB">EPUB</option>
                    <option value="PDF">PDF</option>
                    <option value="AZW3">AZW3</option>
                    <option value="MOBI">MOBI</option>
                </select>
            </div>
            <div class="form-row"><label>Replacement Cost (₱)</label><input type="number" id="ebookReplacementCost" value="0.00" min="0" step="0.01"></div>
            <div class="form-actions">
                <button class="btn-primary" onclick="addEbook()">Save</button>
                <button class="cancel-btn" onclick="hideAllForms()">Cancel</button>
            </div>
        </div>

        <div id="formJournal" class="form-box">
            <h3>Add Journal</h3>
            <div class="form-row"><label>Title *</label><input type="text" id="journalTitle" placeholder="Title"></div>
            <div class="form-row"><label>Author *</label><input type="text" id="journalAuthor" placeholder="Author"></div>
            <div class="form-row"><label>ISSN</label><input type="text" id="journalISBN" placeholder="ISSN (optional)"></div>
            <div class="form-row"><label>Description</label><textarea id="journalDesc" placeholder="Description"></textarea></div>
            <div class="form-row"><label>Genre</label><input type="text" id="journalGenre" placeholder="Genre"></div>
            <div class="form-row"><label>Publish Date</label><input type="date" id="journalPublishDate"></div>
            <div class="form-row"><label>Publisher *</label><input type="text" id="journalPublisher" placeholder="Publisher"></div>
            <div class="form-row"><label>Quantity</label><input type="number" id="journalQty" value="1" min="1"></div>
            <div class="form-row"><label>Type</label>
                <select id="journalType">
                    <option value="Magazine">Magazine</option>
                    <option value="Journal">Journal</option>
                    <option value="Newspaper">Newspaper</option>
                </select>
            </div>
            <div class="form-row"><label>Interval</label>
                <select id="journalInterval">
                    <option value="Daily">Daily</option>
                    <option value="Weekly">Weekly</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Quarterly">Quarterly</option>
                </select>
            </div>
            <div class="form-row"><label>Replacement Cost (₱)</label><input type="number" id="journalReplacementCost" value="0.00" min="0" step="0.01"></div>
            <div class="form-actions">
                <button class="btn-primary" onclick="addJournal()">Save</button>
                <button class="cancel-btn" onclick="hideAllForms()">Cancel</button>
            </div>
        </div>

        <div id="editForm" class="form-box">
            <h3>Edit Material</h3>
            <input type="hidden" id="editMaterialID">
            <div class="form-row"><label>Title *</label><input type="text" id="editTitle" placeholder="Title"></div>
            <div class="form-row"><label>Author *</label><input type="text" id="editAuthor" placeholder="Author"></div>
            <div class="form-row"><label>ISBN</label><input type="text" id="editISBN" placeholder="ISBN"></div>
            <div class="form-row"><label>Description</label><textarea id="editDesc" placeholder="Description"></textarea></div>
            <div class="form-row"><label>Genre</label><input type="text" id="editGenre" placeholder="Genre"></div>
            <div class="form-row"><label>Publish Date</label><input type="date" id="editPublishDate"></div>
            <div class="form-row"><label>Publisher *</label><input type="text" id="editPublisher" placeholder="Publisher"></div>
            <div class="form-row"><label>Total Qty</label><input type="number" id="editTotalQty"></div>
            <div class="form-row"><label>Available Qty</label><input type="number" id="editAvailableQty"></div>
            <div class="form-row"><label>Replacement Cost (₱)</label><input type="number" id="editReplacementCost" value="0.00" min="0" step="0.01"></div>
            <div id="ebookFields" style="display:none;">
                <div class="form-row"><label>EBook Format</label>
                    <select id="editEbookFormat">
                        <option value="">N/A</option>
                        <option value="EPUB">EPUB</option>
                        <option value="PDF">PDF</option>
                        <option value="AZW3">AZW3</option>
                        <option value="MOBI">MOBI</option>
                    </select>
                </div>
            </div>
            <div id="journalFields" style="display:none;">
                <div class="form-row"><label>Journal Type</label>
                    <select id="editJournalType">
                        <option value="">N/A</option>
                        <option value="Magazine">Magazine</option>
                        <option value="Journal">Journal</option>
                        <option value="Newspaper">Newspaper</option>
                    </select>
                </div>
                <div class="form-row"><label>Journal Interval</label>
                    <select id="editJournalInterval">
                        <option value="">N/A</option>
                        <option value="Daily">Daily</option>
                        <option value="Weekly">Weekly</option>
                        <option value="Monthly">Monthly</option>
                        <option value="Quarterly">Quarterly</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn-primary" onclick="updMaterial()">Update</button>
                <button class="cancel-btn" onclick="hideAllForms()">Cancel</button>
            </div>
        </div>

    </div>
</div>

<main>
    <div style="display:flex;gap:8px;margin-bottom:14px;">
        <button class="btn-primary" id="btnAddBook">+ Book</button>
        <button class="btn-primary" id="btnAddEbook">+ EBook</button>
        <button class="btn-primary" id="btnAddJournal">+ Journal</button>
    </div>

    <input type="text" class="tbl-search" id="searchInput" placeholder="Search materials...">

    <div class="section-label" style="margin-top:14px;">Books</div>
    <table id="materialTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Publisher</th>
                <th>Genre</th>
                <th>Total</th>
                <th>Available</th>
                <th>Cost</th>
                <th>Date Added</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="section-label" style="margin-top:14px;">EBooks</div>
    <table id="ebookTable">
        <thead>
            <tr><th>ID</th><th>Type</th><th>Title</th><th>Author</th><th>ISBN</th><th>Publisher</th><th>Genre</th><th>Cost</th><th>Date Added</th><th>Actions</th></tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="section-label" style="margin-top:14px;">Journals</div>
    <table id="journalTable">
        <thead>
            <tr><th>ID</th><th>Type</th><th>Title</th><th>Author</th><th>ISBN</th><th>Publisher</th><th>Type</th><th>Total</th><th>Available</th><th>Cost</th><th>Date Added</th><th>Actions</th></tr>
        </thead>
        <tbody></tbody>
    </table>
</main>

<script src="app/Views/Admin/logMaterial.js"></script>
<script src="app/Views/Auth/logAuth.js"></script>
</body>
</html>