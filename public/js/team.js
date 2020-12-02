var tabHideUser = [];
var tabUserIds = [];


$('#add-users').click(function () {
    //Je récupère le numéro du futur champ que je vais créer
    const index = +$('#users-widgets-count').val();
    //console.log(index);
    $('#users-widgets-count').val(index + 1);

    //$('#add-users').attr('disabled', true);
    //Je récupère le prototype des entrées(champs) et je remplace dans ce
    //prototype toutes les expressions régulières (drapeau g) "___name___" (/___name___/) par l'index
    const tmpl = $('#team_users').data('prototype').replace(/__name__/g, index);
    //console.log(tmpl);

    //J'ajoute à la suite de la div contenant le sous-formulaire ce code
    $('#team_users').append(tmpl).ready(() => {

        var firstNameId = '#team_users_' + index + '_firstName';
        var userPriceId = '#team_users_' + index + '_userPrice';
        var userSkuId = '#team_users_' + index + '_userSku';
        // var userDescriptionId = '#team_users_' + index + '_userDescription';
        // var userHasStockId = '#team_users_' + index + '_userHasStock';

        $(firstNameId).select2({
            width: '100%',
            //height: '300%',
            //dropdownCssClass: "custom-select"
        });

        handleDeleteButton();
    });


});


handleDeleteButton();
updateCounter();

function updateCounter() {
    const usersCounter = +$('#team_users div.nbItems').length;
    //const reductionsCounter = +$('#user_reductions div.form-group').length;

    $('#users-widgets-count').val(usersCounter);
    //$('#reductions-widgets-count').val(reductionsCounter);
    //console.log('widgets-count = ' + $('#users-widgets-count').val());
}

function handleDeleteButton() {
    //Je gère l'action du click sur les boutton possédant l'attribut data-action = "delete"
    $('button[data-action="delete"]').click(function () {
        //Je récupère l'identifiant de la cible(target) à supprimer en recherchant 
        //dans les attributs data-[quelque chose](grâce à dataset) celui dont quelque chose = target (grâce à target)
        const target = this.dataset.target;
        var str = String(target);
        var subItems = String('_users_');
        var posItems = str.indexOf(subItems);

        var index = 0;
        if (posItems > 0) {
            //console.log(str.substr(posItems + subItems.length));
            index = parseInt(str.substr(posItems + subItems.length));

            tabHideUser[index] = '';


        }

        //console.log(target);
        $(target).remove();
        //updateCounter();
    });
};
