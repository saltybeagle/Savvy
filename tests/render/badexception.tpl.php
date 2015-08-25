You should never see this

<?php
print_r(array_keys(get_defined_vars()));

throw new Exception("Error Processing Request", 1);
