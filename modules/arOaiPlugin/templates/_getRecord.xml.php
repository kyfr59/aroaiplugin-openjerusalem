<?php if ($errorCode): ?>
  <error code="<?php echo $errorCode ?>"><?php echo $errorMsg ?></error>
<?php else: ?>
  <?php if (QubitAcl::check($record, 'read')): ?>
    <GetRecord>
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
        <extra>
          <referenceCode><![CDATA[<?php echo $record->referenceCode ?>]]></referenceCode>
          <repository><![CDATA[<?php echo esc_specialchars(strval($record->repository->authorizedFormOfName)) ?>]]></repository>
          <?php foreach ($record->language as $code): ?>
            <languagesOfMaterials><![CDATA[<?php echo format_language($code) ?>]]></languagesOfMaterials>
            <?php endforeach; ?>            
          <?php foreach ($record->getNotesByType(array('noteTypeId' => QubitTerm::PUBLICATION_NOTE_ID)) as $note): ?>
            <publicatonNotes><![CDATA[<?php echo $note->getContent() ?>]]></publicatonNotes>
          <?php endforeach; ?>
          <?php foreach ($record->getDates() as $item): ?>
            <dates>
              <date><![CDATA[<?php echo $date = $item->getDate(); ?>]]></date>
              <?php
                $startEndDate = '';
                $startDate    = Qubit::renderDate($item->startDate);
                $endDate      = Qubit::renderDate($item->endDate);
                if ($startDate && $endDate)
                  $startEndDate = $startDate. ' - '.$endDate;
                elseif($startDate)
                  $startEndDate = $startDate;
                elseif($endDate)
                  $startEndDate = $endDate;
              ?>
              <startEndDate><![CDATA[<?php echo $startEndDate; ?>]]></startEndDate>
            </dates>
          <?php endforeach; ?>

          <?php foreach ($record->getCreators() as $item): ?>
            <creatorLink><![CDATA[<?php echo url_for($item, array('absolute' => true)) ?>]]></creatorLink>
          <?php endforeach; ?>          

          <?php if (count($record->getCreators())>0): ?>
            <?php foreach ($record->getCreators() as $item): ?>
              <creatorLink><![CDATA[<?php echo url_for($item, array('absolute' => true)) ?>]]></creatorLink>
            <?php endforeach; ?>
          <?php else: ?>  
            <?php
            foreach ($record->ancestors->andSelf()->orderBy('rgt') as $ancestor)
                  if (0 < count($ancestor->getCreators()))
                    break;
            ?>
            <?php foreach ($ancestor->getCreators() as $item): ?>
              <?php if (!isset($actorsShown[$item->id])): ?>
                <?php $actorsShown[$item->id] = true; ?>
                  <creatorLink><![CDATA[<?php echo url_for($item, array('absolute' => true)) ?>]]></creatorLink>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>  

          <arrangement><![CDATA[<?php echo $record->getArrangement() ?>]]></arrangement>
          <?php foreach ($record->relationsRelatedBysubjectId as $item): ?>
            <?php if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id): ?>
                <relatedDescriptions><?php echo render_title($item->object) ?> [[<?php echo url_for($item->object, array('absolute' => true)) ?>]]</relatedDescriptions>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php foreach ($record->relationsRelatedByobjectId as $item): ?>
            <?php if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id): ?>
              <relatedDescriptions><?php echo render_title($item->subject) ?>  [[<?php echo url_for($item->subject, array('absolute' => true)) ?>]]</relatedDescriptions>
            <?php endif; ?>
          <?php endforeach; ?>          

          <?php if ($record->levelOfDescription == 'Fonds' || $record->levelOfDescription == 'Series'): ?>

            <archivalHistory><![CDATA[<?php echo $record->getArchivalHistory() ?>]]></archivalHistory>
            <aquisition><![CDATA[<?php echo $record->getAcquisition() ?>]]></aquisition>
            <appraisal><![CDATA[<?php echo $record->getAppraisal() ?>]]></appraisal>
            <accruals><![CDATA[<?php echo $record->getAccruals() ?>]]></accruals>
            <findingAids><![CDATA[<?php echo $record->getFindingAids() ?>]]></findingAids>
            <accessCondition><![CDATA[<?php echo $record->getAccessConditions(array('cultureFallback' => true)) ?>]]></accessCondition>
            <locationOfOriginals><![CDATA[<?php echo $record->getLocationOfOriginals() ?>]]></locationOfOriginals>
            <locationOfCopies><![CDATA[<?php echo $record->getLocationOfCopies() ?>]]></locationOfCopies>
            <rules><![CDATA[<?php echo $record->getRules() ?>]]></rules>
            <descriptionStatus><![CDATA[<?php echo $record->descriptionStatus ?>]]></descriptionStatus>
            <datesOfCreation><![CDATA[<?php echo $record->getRevisionHistory() ?>]]></datesOfCreation>
            <?php foreach ($record->languageOfDescription as $code): ?>
              <languagesOfDescription><![CDATA[<?php echo format_language($code) ?>]]></languagesOfDescription>
            <?php endforeach; ?>
            <?php foreach ($record->scriptOfDescription as $code): ?>
              <scriptsOfDescription><![CDATA[<?php echo format_language($code) ?>]]></scriptsOfDescription>
            <?php endforeach; ?>
            <?php foreach ($record->getNotesByType(array('noteTypeId' => QubitTerm::ARCHIVIST_NOTE_ID)) as $item): ?>
              <archivistsNotes><![CDATA[<?php echo $item->getContent(array('cultureFallback' => true)) ?>]]></archivistsNotes>
            <?php endforeach; ?>
            <?php foreach ($record->language as $code): ?>
              <languagesOfMaterials><![CDATA[<?php echo format_language($code) ?>]]></languagesOfMaterials>
            <?php endforeach; ?>            
            
          <?php else: ?>

            <?php foreach ($record->getProperties(null, 'alternativeIdentifiers') as $item): ?>
              <alternativeIdentifiers><![CDATA[<?php echo render_value($item->name) .' - '. render_value($item->value) ?>]]></alternativeIdentifiers>
            <?php endforeach; ?>
            <?php foreach ($record->getSubjectAccessPoints() as $item): ?>
            <?php foreach ($item->term->ancestors->andSelf()->orderBy('lft') as $key => $subject): ?>
              <?php if (QubitTerm::ROOT_ID == $subject->id) continue; ?>
              <subjectAccessPoints><![CDATA[<?php echo $subject->__toString() ?>]]></subjectAccessPoints>
            <?php endforeach; ?>
            <?php endforeach; ?>
            <?php foreach ($record->getPlaceAccessPoints() as $item): ?>
            <?php foreach ($item->term->ancestors->andSelf()->orderBy('lft') as $key => $subject): ?>
              <?php if (QubitTerm::ROOT_ID == $subject->id) continue; ?>
              <placeAccessPoints><![CDATA[<?php echo $subject->__toString() ?>]]></placeAccessPoints>
            <?php endforeach; ?>
            <?php endforeach; ?>
            <?php foreach ($record->relationsRelatedBysubjectId as $item): ?>
            <?php if (isset($item->type) && QubitTerm::NAME_ACCESS_POINT_ID == $item->type->id): ?>
              <nameAccessPoints><![CDATA[<?php echo $item->object ?>]]></nameAccessPoints>
            <?php endif; ?>
            <?php endforeach; ?>

          <?php endif; ?>
          
        </extra>
        <?php if (count($record->digitalObjects)): ?>
          <?php include('_about.xml.php') ?>
        <?php endif; ?>
      </record>
    </GetRecord>
  <?php endif; ?>
<?php endif; ?>
