function validateCommitForm() {
  if (checkValue('comment', 'Commit Comment') ||
      checkValue('accesskey', 'Commit Access Key') ||
      checkValue('authorname', 'Author Name') ||
      checkValue('authoremail', 'Author Email')) {
    return false;
  }
}

function checkValue(fieldName, label) {
  var x = document.forms["repoform"][fieldName].value;
  if (x == null || x == "") {
    alert(label + " must be filled out");
    return true;
  }

  return false;
}

function toggleSelect() {

  var commits = document.repoform["commits[]"];
  if (document.repoform.toggle.checked) {
    for (i = 0; i < commits.length; i++) {
        commits[i].checked = true;
    }
  } else {
    for (i = 0; i < commits.length; i++) {
        commits[i].checked = false;
    }
  }
}
