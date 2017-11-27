<?php if ($recordsCount == 0): ?>
  <error code="noRecordsMatch">The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.</error>
<?php else: ?>
  <ListRecords>
  <?php foreach ($publishedRecords as $record): ?>
    <?php if (QubitAcl::check($record, 'read') && array_search($record->getOaiIdentifier(), $identifiersWithMissingCacheFiles) === false): ?>
      <record>
        <header>
          <identifier><?php echo $record->getOaiIdentifier() ?></identifier>
          <datestamp><?php echo QubitOai::getDate($record->getUpdatedAt())?></datestamp>
          <setSpec><?php echo $record->getCollectionRoot()->getOaiIdentifier()?></setSpec>
        </header>
        <metadata>
          <?php if ($metadataPrefix == 'oai_dc' && !arOaiPluginComponent::cachedMetadataExists($record, $metadataPrefix)): ?>
            <?php echo get_component('sfDcPlugin', 'dc', array('resource' => $record)) ?>
          <?php else: ?>
            <?php arOaiPluginComponent::includeCachedMetadata($record, $metadataPrefix) ?>
          <?php endif; ?>
        </metadata>
        <translations>
          <?php  $translations = arOaiPluginComponent::getTranslations($record); ?>
          <?php foreach($translations as $translation): ?>
            <translation>
              <code><?php echo $translation; ?></code>
              <label><?php echo format_language($translation); ?></label>
            </translation>
          <?php endforeach; ?>
        </translations>
        <hierarchy>
          <atomId><?php echo $record->id ?></atomId>
          <parentId><?php echo $record->parent->id ?></parentId>
          <topParentId><?php echo arOaiPluginComponent::getTopLevelParent($record) ?></topParentId>
          <level><?php echo arOaiPluginComponent::getItemLevel($record) ?></level>
          <order><?php echo $record->lft ?></order>
          <type><?php echo $record->levelOfDescription ?></type>
          <oaiIdentifier><?php echo $record->getOaiIdentifier() ?></oaiIdentifier>
          <url><?php echo esc_specialchars(sfConfig::get('app_siteBaseUrl') .'/'.$record->slug) ?></url>
        </hierarchy>
        <files>
        <?php
        if(count($objects = $record->digitalObjects)) {
          foreach($objects as $object) {
            echo '<url name="'.$object->getName().'" type="master">'.$object->getPublicPath().'</url>';
          }
        }
        ?>
        </files>
        <extra>
          <referenceCode><?php echo $record->referenceCode ?></referenceCode>
          <repository><?php echo esc_specialchars(strval($record->repository->authorizedFormOfName)) ?></repository>
          <publicatonNotes>
          <?php foreach ($record->getNotesByType(array('noteTypeId' => QubitTerm::PUBLICATION_NOTE_ID)) as $note): ?>
            <publicatonNote><?php echo $note->getContent() ?></publicatonNote>
          <?php endforeach; ?>
          </publicatonNotes>
          <?php if ($record->levelOfDescription == 'Fonds' || $record->levelOfDescription == 'Series'): ?>
            <archivalHistory><?php echo $record->getArchivalHistory() ?></archivalHistory>
            <aquisition><?php echo $record->getAcquisition() ?></aquisition>
            <appraisal><?php echo $record->getAppraisal() ?></appraisal>
            <accruals><?php echo $record->getAccruals() ?></accruals>
            <arrangement><?php echo $record->getArrangement() ?></arrangement>
            <findingAids><?php echo $record->getFindingAids() ?></findingAids>
            <locationOfOriginals><?php echo $record->getLocationOfOriginals() ?></locationOfOriginals>
            <locationOfCopies><?php echo $record->getLocationOfCopies() ?></locationOfCopies>
            <rules><?php echo $record->getRules() ?></rules>
            <descriptionStatus><?php echo $record->descriptionStatus ?></descriptionStatus>
            <datesOfCreation><?php echo $record->getRevisionHistory() ?></datesOfCreation>
            <languagesOfDescription>
            <?php foreach ($record->languageOfDescription as $code): ?>
              <language><?php echo format_language($code) ?></language>
            <?php endforeach; ?>
            </languagesOfDescription>
            <scriptsOfDescription>
            <?php foreach ($record->scriptOfDescription as $code): ?>
              <script><?php echo format_language($code) ?></script>
            <?php endforeach; ?>
            </scriptsOfDescription>
          <?php else: ?>
            <alternativeIdentifiers>
            <?php foreach ($record->getProperties(null, 'alternativeIdentifiers') as $item): ?>
              <identifier><?php echo render_value($item->name) ?></identifier>
            <?php endforeach; ?>
            </alternativeIdentifiers>
            <languagesOfMaterials>
            <?php foreach ($record->language as $code): ?>
              <language><?php echo format_language($code) ?></language>
             <?php endforeach; ?>
            </languagesOfMaterials>
            <relatedDescriptions>
              <?php foreach ($record->relationsRelatedBysubjectId as $item): ?>
                <?php if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id): ?>
                  <description><?php echo render_title($item->object) ?></description>
                <?php endif; ?>
              <?php endforeach; ?>
            </relatedDescriptions>
            <subjectAccessPoints>
            <?php foreach ($record->getSubjectAccessPoints() as $item): ?>
            <?php foreach ($item->term->ancestors->andSelf()->orderBy('lft') as $key => $subject): ?>
              <?php if (QubitTerm::ROOT_ID == $subject->id) continue; ?>
              <subject><?php echo $subject->__toString() ?></subject>
            <?php endforeach; ?>
            <?php endforeach; ?>
            </subjectAccessPoints>
            <subjectAccessPoints>
            <?php foreach ($record->getSubjectAccessPoints() as $item): ?>
            <?php foreach ($item->term->ancestors->andSelf()->orderBy('lft') as $key => $subject): ?>
              <?php if (QubitTerm::ROOT_ID == $subject->id) continue; ?>
              <point><?php echo $subject->__toString() ?></point>
            <?php endforeach; ?>
            <?php endforeach; ?>
            </subjectAccessPoints>
            <placeAccessPoints>
            <?php foreach ($record->getPlaceAccessPoints() as $item): ?>
            <?php foreach ($item->term->ancestors->andSelf()->orderBy('lft') as $key => $subject): ?>
              <?php if (QubitTerm::ROOT_ID == $subject->id) continue; ?>
              <point><?php echo $subject->__toString() ?></point>
            <?php endforeach; ?>
            <?php endforeach; ?>
            </placeAccessPoints>
            <nameAccessPoints>
            <?php foreach ($record->relationsRelatedBysubjectId as $item): ?>
            <?php if (isset($item->type) && QubitTerm::NAME_ACCESS_POINT_ID == $item->type->id): ?>
              <point><?php echo $item->object ?></point>
            <?php endif; ?>
            <?php endforeach; ?>
            </nameAccessPoints>
          <?php endif; ?>
        </extra>
        <?php include('_about.xml.php') ?>
      </record>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php foreach ($identifiersWithMissingCacheFiles as $identifier): ?>
    <error code="cannotDisseminateFormat">The metadata format identified by the value given for the metadataPrefix argument is available for item <?php echo $identifier; ?>.</error>
  <?php endforeach; ?>
  <?php if ($remaining > 0): ?>
    <resumptionToken><?php echo $resumptionToken ?></resumptionToken>
  <?php endif; ?>
  </ListRecords>
<?php endif; ?>
