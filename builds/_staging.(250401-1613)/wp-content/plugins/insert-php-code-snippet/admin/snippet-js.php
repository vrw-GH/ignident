<script type="text/javascript">
$ = jQuery;
$(document).ready(() => {
  const XYZ_IPS_INSERTION_LOCATION = <?php echo json_encode(XYZ_IPS_INSERTION_LOCATION); ?>;
  const XYZ_IPS_INSERTION_METHOD = <?php echo json_encode(XYZ_IPS_INSERTION_METHOD); ?>;
  const XYZ_IPS_INSERTION_LOCATION_TYPE = <?php echo json_encode(XYZ_IPS_INSERTION_LOCATION_TYPE); ?>;
  let xyz_ips_snippetType = $('#xyz_ips_snippetType').val();
  let xyz_ips_insertionMethod = $('#xyz_ips_insertionMethod').val();

  /* General functions starts */
  const toggleListItemVisibility = (locationKey, showHide) => {
    const listItem = $(`#xyz_ips_uniq_list li[data-value="${XYZ_IPS_INSERTION_LOCATION[locationKey]}"]`);
    listItem.css('display', showHide === 'show' ? 'block' : 'none');
  };

  const getInsertionLocationText = (xyz_ips_insertionLocation) => {
    xyz_ips_insertion_location_txt =
      (xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['ADMIN_RUN_ON_HEADER']) ? 'Admin - Run On Header' :
      (xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['ADMIN_RUN_ON_FOOTER']) ? 'Admin - Run On Footer' :
      (xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['FRONTEND_RUN_ON_HEADER']) ? 'Front End - Run On Header' :
      (xyz_ips_insertionLocation == XYZ_IPS_INSERTION_LOCATION['FRONTEND_RUN_ON_FOOTER']) ? 'Front End - Run On Footer' :null;
   
      const targetElement = $('#xyz_ips_insertion-location-txt');
    
    if (targetElement.length > 0 && xyz_ips_insertion_location_txt) {
      $('#xyz_ips_insertion-location-txt').text(xyz_ips_insertion_location_txt);
    }
  };
  /* General functions ends */


  if (xyz_ips_insertionMethod != XYZ_IPS_INSERTION_METHOD['AUTOMATIC']) {
    $('#xyz_ips_insertionLocationTR').hide();
  } else {
    $('#xyz_ips_insertionLocationTR').show();
  }

  $('#xyz_ips_insertionMethod').change((e) => {
    let xyz_ips_insertionMethod = $(e.target).val();

    if (xyz_ips_insertionMethod == XYZ_IPS_INSERTION_METHOD['AUTOMATIC']) {
      $('#xyz_ips_insertionLocationTR').show();

  
    } else {
      $('#xyz_ips_insertionLocationTR').hide();
  
    }
  });

  let xyz_ips_insertionLocation = $('#xyz_ips_insertionLocation').val();
  let xyz_ips_insertion_location_txt = 'Select Insertion Location';
  if (xyz_ips_insertionLocation > 0) {
    getInsertionLocationText(xyz_ips_insertionLocation);
  }


  const listItems = document.querySelectorAll('.xyz_ips_li_option');
  listItems.forEach(item => {
    item.addEventListener('click', () => {
      listItems.forEach(li => li.classList.remove('selected'));
      item.classList.add('selected');
      const selectedValue = item.getAttribute('data-value');
      $('#xyz_ips_insertionLocation').val(selectedValue);
      getInsertionLocationText(selectedValue);

  
    });
  });
  $('#xyz_ips_uniq_list').hide();
  $('.xyz_ips_insertionLocationDiv').on('click', () => {
    $('#xyz_ips_uniq_list').toggle();
  });
});

</script>
