                          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($cells[$x / 2][($y / 2)])): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cells[$x / 2][($y / 2)]->name == 'Passable Land'): ?>
                              landCell
                            <?php elseif($cells[$x / 2][($y / 2)]->name == 'Trees'): ?>
                              treeCell
                            <?php elseif($cells[$x / 2][($y / 2)]->name == 'Water'): ?>
                              waterCell
                            <?php else: ?>
                              rockCell
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/mapgen/tileclassname.blade.php ENDPATH**/ ?>