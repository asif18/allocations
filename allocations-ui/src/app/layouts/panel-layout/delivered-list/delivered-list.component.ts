/**
 * Copyrights Allocations 2021. All rights reserved
 *
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your
 * business/personal purpose and not to sell or modify
 *
 * Developed by: Mohamed Asif
 * Email: mohamedasif18@gmail.com
 */
import { Component, ViewChild, OnInit, AfterViewInit, ElementRef, OnDestroy, EventEmitter, TemplateRef } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { MatDialog, MatDialogRef } from '@angular/material';
import { SelectionModel } from '@angular/cdk/collections';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { merge, Subscription, noop, of as observableOf, forkJoin } from 'rxjs';
import { catchError, map, startWith, switchMap, finalize } from 'rxjs/operators';
import { Title } from '@angular/platform-browser';
import { faFileExcel, faSearch, faTimes, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
import * as _ from 'lodash';
import { SnackbarService, AllocationsService, DestinationsService, DataShareService, UtilityService, YardsService } 
  from '../../../_services';
import { DefaultListApiParams, DefaultApiResponse } from '../../../_models';
import { BottomSheetFileDownloadPromptComponent } from '../../../_components/bottom-sheet-file-download-prompt';
import { ConfirmationDialogComponent } from '../../../_components/confirmation-dialog';

declare interface Allocations {
  allocationStatus: string;
  chassis_number: string;
  close_date: string;
  container_number: string;
  createdBy: string;
  created_datetime: string;
  delivery_date: string;
  destinationCode: string;
  destinationName: string;
  drop_date: string;
  id: string;
  is_rail_bill: string;
  open_date: string;
  seal_number: string;
  status: string;
  to: string;
  yardCode: string;
  yardName: string;
}

declare interface FormDependencyData {
  destinations: object[];
  yards: object[];
  tos: object[];
  statuses: object[];
}

@Component({
  selector: 'app-delivered-list',
  templateUrl: './delivered-list.component.html',
  styleUrls: ['./delivered-list.component.scss']
})
export class DeliveredListComponent implements OnInit, AfterViewInit, OnDestroy  {

  public displayedColumns: string[] = ['container_number', 'destinationName', 'yardName', 'chassis_number',
    'seal_number', 'openDate', 'expiryDate', 'deliveryDate', 'deliveryUpdatedBy', 'action'];
  public data: Allocations[] = [];
  public isFormLoading: boolean = false;
  public form: FormGroup;
  public deliveryForm: FormGroup;
  public resultsLength = 0;
  public pageSize: number = 10;
  private subscriptions: Subscription[] = [];
  public faIcons = { faFileExcel, faSearch, faTimes, faTrashAlt };
  public formDependencyData: FormDependencyData = {
    destinations: [],
    yards: [],
    tos: [],
    statuses: []
  };
  private onSearch: EventEmitter<any> = new EventEmitter();
  
  @ViewChild(MatPaginator, {static: true}) paginator: MatPaginator;
  @ViewChild(MatSort, {static: true}) sort: MatSort;

  constructor(
    private titleService: Title,
    private dataShareService: DataShareService,
    private bottomSheet: MatBottomSheet,
    private utilityService: UtilityService,
    private formBuilder: FormBuilder,
    private snackBar: SnackbarService,
    private dialog: MatDialog,
    private allocationsService: AllocationsService,
    private destinationsService: DestinationsService,
    private yardsService: YardsService) {}

  ngOnInit(): void {
    this.titleService.setTitle('Delivered List');
    this.subscriptions.push(this.dataShareService.receivedMessage.subscribe(data => {
      if (_.isNull(data)) {
        return;
      }

      const params: DefaultListApiParams =  {
        searchBy: null,
        startFrom: this.paginator.pageIndex,
        endTo: this.paginator.pageIndex + this.paginator.pageSize,
        sortBy: this.sort.active,
        sortDirection: this.sort.direction,
      };

      switch (data) {
        case 'downloadVisibled':
          break;

        case 'downloadFiltered':
          params.startFrom = null;
          params.endTo = null;
          break;

        case 'downloadAll':
          params.searchBy = null;
          params.startFrom = null;
          params.endTo = null;
          break;
      }

      this.isFormLoading = true;
      this.allocationsService.exportAllocations(params)
        .pipe(finalize(() => this.isFormLoading = false))
        .subscribe((response) => {
          const fileName = this.utilityService.getFileNameFromHttpResponse(response);
          const options = { type: response.body.type };
          this.utilityService.downloadBlobFile(response.body, options, fileName);
        });
    }));

    this.initForm();
    this.getFormInitialData();
  }

  ngAfterViewInit() {
    // If the user changes the sort order, reset back to the first page.
    this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

    merge(this.sort.sortChange, this.paginator.page, this.onSearch)
      .pipe(
        startWith({}),
        switchMap(() => {
          this.isFormLoading = true;
          return this.getAllocations(this.form.value);
        }),
        map(response => {
          this.isFormLoading = false;
          this.resultsLength = _.size(response.data.items);
          return response.data.items;
        }),
        catchError(() => {
          return observableOf([]);
        })
      ).subscribe((data) => {
        this.data = data;
        _.map(this.data, (key) => {
          key.is_rail_bill = (key.is_rail_bill === true) ? 'Yes' : 'No';
        })
      });
  }

  private getAllocations(searchBy: any = null) {
    const params: DefaultListApiParams = {
      searchBy,
      startFrom: this.paginator.pageIndex,
      endTo: this.paginator.pageIndex + this.paginator.pageSize,
      sortBy: this.sort.active,
      sortDirection: this.sort.direction,
    };
    return this.allocationsService.getAllocations(params);
  }

  private getFormInitialData() {
    this.isFormLoading = true;
    const params: DefaultListApiParams = {
      searchBy: null,
      startFrom: null,
      endTo: null,
      sortBy: 'code',
      sortDirection: 'desc',
    };
    const requests = forkJoin(
      this.destinationsService.getDestinations(params),
      this.yardsService.getYards(params),
      this.allocationsService.getAllocationStatuses(_.merge(_.clone(params), {sortBy: 'name'}))
    );

    this.subscriptions.push(requests
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse[]) => {
        _.assign(this.formDependencyData, {
          destinations: response[0].data.items,
          yards: response[1].data.items,
          statuses: response[2].data.items,
          tos: this.allocationsService.getAllTos()
        });
      }));
  }

  private initForm() {
    this.form = this.formBuilder.group({
      containerNumber: new FormControl(null, {
        validators: [
          Validators.minLength(7),
          Validators.maxLength(7),
          Validators.pattern(/^[A-Z0-9]+$/)
        ]
      }),
      destination: new FormControl(null),
      yard: new FormControl(null),
      to: new FormControl(null),
      status: new FormControl('DLY')
    });
  }

  private resetForm(): void {
    this.form.reset();
    this.form.patchValue({
      status: 'DLY'
    })
  }

  get f() {
    return this.form.controls;
  }

  public onClearSearch(): void {
    this.resetForm();
    this.onSearch.emit(this.form.value);
  }

  public onSearchFormSubmit(): void {
    this.paginator.pageIndex = 0;
    this.onSearch.emit(this.form.value);
  }

  openBottomSheet(): void {
    const items: object[] = [
      {
        action: 'downloadVisibled',
        title: 'Download What You See',
        description: 'downloads excel sheet with data that is visible in this page'
      },
      {
        action: 'downloadFiltered',
        title: 'Download Filtered',
        description: 'downloads excel sheet with data that is filtered if any'
      },
      {
        action: 'downloadAll',
        title: 'Download All',
        description: 'downloads excel sheet with entire data'
      }
    ];
    this.bottomSheet.open(BottomSheetFileDownloadPromptComponent, {
      data: {component: 'AllocationsListComponent', items}
    });
  }
  
  public deleteAllocation(id: string) {
    const dialogRef = this.dialog.open(ConfirmationDialogComponent, {
      data:{
        message: 'Are you sure want to delete?',
        buttonText: {
          ok: 'Save',
          cancel: 'No'
        }
      }
    });

    this.subscriptions.push(dialogRef.afterClosed().subscribe((confirmed: boolean) => {
      if (confirmed) {
        this.isFormLoading = true;
        const promise = this.allocationsService.removeAllocation(btoa(id));
        this.subscriptions.push(promise.pipe(finalize(() => this.isFormLoading = false))
          .subscribe((response: DefaultApiResponse) => {
            if (response) {
              this.onSearch.emit(null);
              this.snackBar.show(response.message, response.status ? 'success' : 'danger');
            }
          }, () => noop()));
      }
    }));
  }
  
  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
