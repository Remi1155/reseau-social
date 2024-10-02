function getXMLHttpRequest() {
    var xhr = null;
  
    if (window.XMLHttpRequest || window.ActiveXObject) {
      if (window.ActiveXObject) {
        try {
          xhr = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
      } else {
        xhr = new XMLHttpRequest();
      }
    } else {
      alert("Votre navigateur ne supporte pas l'objet XMLHTTPRequest...");
      return null;
    }
  
    return xhr;
  }
  
  function envoyerReaction(typeReaction, idComment, idCompte) {
    var xhr = getXMLHttpRequest();
  
    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        // Réponse du serveur
        var response = JSON.parse(xhr.responseText);
  
        // Mettre à jour dynamiquement le nombre total de réactions
        document.getElementById("reaction-count-" + idComment).innerText =
          response.totalReactions;
  
        // Réinitialisation les couleurs de tous les boutons de réaction
        document
          .getElementById("jaime-" + idComment)
          .classList.remove("bg-blue-500");
        document
          .getElementById("jadore-" + idComment)
          .classList.remove("bg-blue-500");
        document
          .getElementById("haha-" + idComment)
          .classList.remove("bg-blue-500");
        document
          .getElementById("triste-" + idComment)
          .classList.remove("bg-blue-500");
  
        // Ajout de la classe 'bg-blue-500' au bouton correspondant à la réaction de l'utilisateur
        document
          .getElementById(typeReaction + "-" + idComment)
          .classList.add("bg-blue-500");
      }
    };
  
    xhr.open(
      "GET",
      "../react_comment/insert_reaction.php?type=" +
        typeReaction +
        "&id_comment=" +
        idComment +
        "&id_compte=" +
        idCompte,
      true
    );
    xhr.send(null);
  }
  