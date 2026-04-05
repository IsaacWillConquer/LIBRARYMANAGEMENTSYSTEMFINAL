const CTRL = 'app/Controllers/materialController.php';
var searchTimer;

$(document).ready(function () {
    loadMaterials();

    $('#formBook, #formEbook, #formJournal, #editForm').hide();

    $('#btnAddBook').click(function () {
        $('#editForm, #formEbook, #formJournal').hide();
        $('#formBook').fadeToggle(300);
    });

    $('#btnAddEbook').click(function () {
        $('#editForm, #formBook, #formJournal').hide();
        $('#formEbook').fadeToggle(300);
    });

    $('#btnAddJournal').click(function () {
        $('#editForm, #formBook, #formEbook').hide();
        $('#formJournal').fadeToggle(300);
    });

    $('#searchInput').on('keyup', function () {
        clearTimeout(searchTimer);
        var q = $(this).val().trim();
        searchTimer = setTimeout(function () {
            doMaterialSearch(q);
        }, 300);
    });
});

function hideAllForms() {
    $('#formBook, #formEbook, #formJournal, #editForm').hide();
}

function showError(msg) {
    $('#error_text').text(msg).fadeIn();
}

function hideError() {
    $('#error_text').hide();
}

function loadMaterials() {
    $.ajax({
        url: CTRL,
        method: 'GET',
        data: { action: 'getAll' },
        cache: false,
        dataType: 'json',
        success: function (data) {
            let bookRows = '';
            data.books.forEach(m => {
                bookRows += `<tr>
                    <td>${m.MaterialID}</td>
                    <td>Book</td>
                    <td>${m.Title}</td>
                    <td>${m.Author}</td>
                    <td>${m.ISBN ?? 'N/A'}</td>
                    <td>${m.Publisher}</td>
                    <td>${m.Genre ?? 'N/A'}</td>
                    <td>${m.TotalQuantity}</td>
                    <td>${m.AvailableQuantity}</td>
                    <td>${m.DateAdded}</td>
                    <td>
                        <button onclick="loadEdit(${m.MaterialID})">Edit</button>
                        <button onclick="archiveMaterial(${m.MaterialID})">Archive</button>
                    </td>
                </tr>`;
            });
            $('#materialTable tbody').html(bookRows);

            let ebookRows = '';
            data.ebooks.forEach(m => {
                ebookRows += `<tr>
                    <td>${m.MaterialID}</td>
                    <td>EBook</td>
                    <td>${m.Title}</td>
                    <td>${m.Author}</td>
                    <td>${m.ISBN ?? 'N/A'}</td>
                    <td>${m.Publisher}</td>
                    <td>${m.Genre ?? 'N/A'}</td>
                    <td>${m.TotalQuantity}</td>
                    <td>${m.AvailableQuantity}</td>
                    <td>${m.DateAdded}</td>
                    <td>
                        <button onclick="loadEdit(${m.MaterialID})">Edit</button>
                        <button onclick="archiveMaterial(${m.MaterialID})">Archive</button>
                    </td>
                </tr>`;
            });
            $('#ebookTable tbody').html(ebookRows);

            let journalRows = '';
            data.journals.forEach(m => {
                journalRows += `<tr>
                    <td>${m.MaterialID}</td>
                    <td>Journal</td>
                    <td>${m.Title}</td>
                    <td>${m.Author}</td>
                    <td>${m.ISBN ?? 'N/A'}</td>
                    <td>${m.Publisher}</td>
                    <td>${m.JournalType ?? 'N/A'}</td>
                    <td>${m.TotalQuantity}</td>
                    <td>${m.AvailableQuantity}</td>
                    <td>${m.DateAdded}</td>
                    <td>
                        <button onclick="loadEdit(${m.MaterialID})">Edit</button>
                        <button onclick="archiveMaterial(${m.MaterialID})">Archive</button>
                    </td>
                </tr>`;
            });
            $('#journalTable tbody').html(journalRows);
        }
    });
}

function addBook() {
    hideError();
    let title = $('#bookTitle').val().trim();
    let author = $('#bookAuthor').val().trim();
    let publisher = $('#bookPublisher').val().trim();

    if (!title) return showError('Title is required');
    if (!author) return showError('Author is required');
    if (!publisher) return showError('Publisher is required');

    $.ajax({
        url: CTRL,
        method: 'POST',
        data: {
            action: 'addBook',
            title: title,
            author: author,
            isbn: $('#bookISBN').val().trim(),
            description: $('#bookDesc').val().trim(),
            genre: $('#bookGenre').val().trim(),
            publishDate: $('#bookPublishDate').val(),
            publisher: publisher,
            totalQty: $('#bookQty').val(),
        },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                alert(res.message);
                hideAllForms();
                loadMaterials();
                clearBookForm();
            } else {
                showError(res.message);
            }
        }
    });
}

function clearBookForm() {
    $('#bookTitle, #bookAuthor, #bookISBN, #bookDesc, #bookGenre, #bookPublisher').val('');
    $('#bookQty').val(1);
}

function addEbook() {
    hideError();
    let title = $('#ebookTitle').val().trim();
    let author = $('#ebookAuthor').val().trim();
    let publisher = $('#ebookPublisher').val().trim();

    if (!title) return showError('Title is required');
    if (!author) return showError('Author is required');
    if (!publisher) return showError('Publisher is required');

    $.ajax({
        url: CTRL,
        method: 'POST',
        data: {
            action: 'addEbook',
            title: title,
            author: author,
            isbn: $('#ebookISBN').val().trim(),
            description: $('#ebookDesc').val().trim(),
            genre: $('#ebookGenre').val().trim(),
            publishDate: $('#ebookPublishDate').val(),
            publisher: publisher,
            totalQty: $('#ebookQty').val(),
            ebookFormat: $('#ebookFormat').val(),
        },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                alert(res.message);
                hideAllForms();
                loadMaterials();
                clearEbookForm();
            } else {
                showError(res.message);
            }
        }
    });
}

function clearEbookForm() {
    $('#ebookTitle, #ebookAuthor, #ebookISBN, #ebookDesc, #ebookGenre, #ebookPublisher').val('');
    $('#ebookQty').val(1);
    $('#ebookAccessStart, #ebookAccessEnd').val('');
}

function addJournal() {
    hideError();
    let title = $('#journalTitle').val().trim();
    let author = $('#journalAuthor').val().trim();
    let publisher = $('#journalPublisher').val().trim();

    if (!title) return showError('Title is required');
    if (!author) return showError('Author is required');
    if (!publisher) return showError('Publisher is required');

    $.ajax({
        url: CTRL,
        method: 'POST',
        data: {
            action: 'addJournal',
            title: title,
            author: author,
            isbn: $('#journalISBN').val().trim(),
            description: $('#journalDesc').val().trim(),
            genre: $('#journalGenre').val().trim(),
            publishDate: $('#journalPublishDate').val(),
            publisher: publisher,
            totalQty: $('#journalQty').val(),
            journalType: $('#journalType').val(),
            journalInterval: $('#journalInterval').val(),
        },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                alert(res.message);
                hideAllForms();
                loadMaterials();
                clearJournalForm();
            } else {
                showError(res.message);
            }
        }
    });
}

function clearJournalForm() {
    $('#journalTitle, #journalAuthor, #journalISBN, #journalDesc, #journalGenre, #journalPublisher').val('');
    $('#journalQty').val(1);
}

function loadEdit(id) {
    $.ajax({
        url: CTRL,
        method: 'GET',
        data: { action: 'getOne', id: id },
        dataType: 'json',
        success: function (m) {
            $('#editMaterialID').val(m.MaterialID);
            $('#editTitle').val(m.Title);
            $('#editAuthor').val(m.Author);
            $('#editISBN').val(m.ISBN ?? '');
            $('#editDesc').val(m.Description ?? '');
            $('#editGenre').val(m.Genre ?? '');
            $('#editPublishDate').val(m.PublishDate ?? '');
            $('#editPublisher').val(m.Publisher);
            $('#editTotalQty').val(m.TotalQuantity);
            $('#editAvailableQty').val(m.AvailableQuantity);
            $('#editEbookFormat').val(m.EbookFormat ?? '');
            $('#editAccessStart').val(m.AccessStart ?? '');
            $('#editAccessEnd').val(m.AccessEnd ?? '');
            $('#editJournalType').val(m.JournalType ?? '');
            $('#editJournalInterval').val(m.JournalInterval ?? '');

            hideAllForms();
            $('#editForm').data('typeID', m.TypeID);
            $('#ebookFields, #journalFields').hide();

            if (m.TypeID == 2) $('#ebookFields').show();
            if (m.TypeID == 3) $('#journalFields').show();

            $('#editForm').fadeIn(300);
        }
    });
}

function updMaterial() {
    hideError();
    let materialID = $('#editMaterialID').val();
    let title = $('#editTitle').val().trim();
    let author = $('#editAuthor').val().trim();
    let publisher = $('#editPublisher').val().trim();
    let typeID = $('#editForm').data('typeID');

    if (!title) return showError('Title is required');
    if (!author) return showError('Author is required');
    if (!publisher) return showError('Publisher is required');

    let data = {
        action: 'updMaterial',
        materialID: materialID,
        title: title,
        author: author,
        isbn: $('#editISBN').val().trim(),
        description: $('#editDesc').val().trim(),
        genre: $('#editGenre').val().trim(),
        publishDate: $('#editPublishDate').val(),
        publisher: publisher,
        totalQty: $('#editTotalQty').val(),
        availableQty: $('#editAvailableQty').val(),
    };

    if (typeID == 2) data.ebookFormat = $('#editEbookFormat').val();
    if (typeID == 3) {
        data.journalType = $('#editJournalType').val();
        data.journalInterval = $('#editJournalInterval').val();
    }

    $.ajax({
        url: CTRL,
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                alert(res.message);
                $('#editForm').hide();
                loadMaterials();
            } else {
                showError(res.message);
            }
        }
    });
}

function archiveMaterial(id) {
    if (!confirm('Archive this material? Its data will be saved to archives.')) return;
    $.ajax({
        url: CTRL,
        method: 'POST',
        data: { action: 'archiveMaterial', materialID: id },
        dataType: 'json',
        success: function (res) {
            alert(res.message);
            if (res.success) loadMaterials();
        }
    });
}

function doMaterialSearch(q) {
    if (q === '') {
        loadMaterials();
        return;
    }
    $.ajax({
        url: CTRL,
        method: 'GET',
        data: { action: 'search', q: q },
        cache: false,
        dataType: 'json',
        success: function (data) {
            renderMaterials(data);
        }
    });
}

function renderMaterials(data) {
    let bookRows = '';
    if (data.books && data.books.length > 0) {
        data.books.forEach(m => {
            bookRows += `<tr>
                <td>${m.MaterialID}</td><td>Book</td><td>${m.Title}</td>
                <td>${m.Author}</td><td>${m.ISBN ?? 'N/A'}</td>
                <td>${m.Publisher}</td><td>${m.Genre ?? 'N/A'}</td>
                <td>${m.TotalQuantity}</td><td>${m.AvailableQuantity}</td>
                <td>${m.DateAdded}</td>
                <td>
                    <button onclick="loadEdit(${m.MaterialID})">Edit</button>
                    <button onclick="archiveMaterial(${m.MaterialID})">Archive</button>
                </td>
            </tr>`;
        });
    } else {
        bookRows = '<tr><td colspan="11">No books found</td></tr>';
    }
    $('#materialTable tbody').html(bookRows);

    let ebookRows = '';
    if (data.ebooks && data.ebooks.length > 0) {
        data.ebooks.forEach(m => {
            ebookRows += `<tr>
                <td>${m.MaterialID}</td><td>EBook</td><td>${m.Title}</td>
                <td>${m.Author}</td><td>${m.ISBN ?? 'N/A'}</td>
                <td>${m.Publisher}</td><td>${m.Genre ?? 'N/A'}</td>
                <td>${m.TotalQuantity}</td><td>${m.AvailableQuantity}</td>
                <td>${m.DateAdded}</td>
                <td>
                    <button onclick="loadEdit(${m.MaterialID})">Edit</button>
                    <button onclick="archiveMaterial(${m.MaterialID})">Archive</button>
                </td>
            </tr>`;
        });
    } else {
        ebookRows = '<tr><td colspan="11">No ebooks found</td></tr>';
    }
    $('#ebookTable tbody').html(ebookRows);

    let journalRows = '';
    if (data.journals && data.journals.length > 0) {
        data.journals.forEach(m => {
            journalRows += `<tr>
                <td>${m.MaterialID}</td><td>Journal</td><td>${m.Title}</td>
                <td>${m.Author}</td><td>${m.ISBN ?? 'N/A'}</td>
                <td>${m.Publisher}</td><td>${m.JournalType ?? 'N/A'}</td>
                <td>${m.TotalQuantity}</td><td>${m.AvailableQuantity}</td>
                <td>${m.DateAdded}</td>
                <td>
                    <button onclick="loadEdit(${m.MaterialID})">Edit</button>
                    <button onclick="archiveMaterial(${m.MaterialID})">Archive</button>
                </td>
            </tr>`;
        });
    } else {
        journalRows = '<tr><td colspan="11">No journals found</td></tr>';
    }
    $('#journalTable tbody').html(journalRows);
}