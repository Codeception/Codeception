<html>
<body>
<form action="/form/complex" method="post" enctype="multipart/form-data" name="package_csv_form" class="form">
    <dl>
        <dd>
            <label>
                <span class="label">XLS file</span>
                <input type="hidden" name="MAX_FILE_SIZE" value="2097152" id="MAX_FILE_SIZE">
                <input type="file" name="xls_file" id="xls_file">
            </label>
        </dd>
    </dl>
    <dl>
        <dd class="last">
            <input type="hidden" name="form_name" value="package_csv_form" id="form_name">
            <input type="submit" name="submit" id="submit" value="Upload packages" class="submit">
            <a href="#" class="cancel_link">Cancel</a>
        </dd>
    </dl>
</form>
</body>
</html>