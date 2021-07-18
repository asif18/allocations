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
 import { Component, OnInit, OnDestroy } from '@angular/core';
 import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
 import { noop, Subscription, forkJoin } from 'rxjs';
 import { finalize } from 'rxjs/operators';
 import { ActivatedRoute } from '@angular/router';
 import { Title } from '@angular/platform-browser';
 import * as _ from 'lodash';
 import * as moment from 'moment';
 import {
   SnackbarService,
   YardsService,
   DestinationsService,
   AllocationsService
   } from '../../../_services';
 import { DefaultApiResponse, DefaultListApiParams } from '../../../_models';

 
 declare interface FormDependencyData {
   destinations: object[];
   yards: object[];
   tos: object[];
   statuses: object[];
   maxDate: Date;
 }

@Component({
  selector: 'app-add-loads',
  templateUrl: './add-loads.component.html',
  styleUrls: ['./add-loads.component.scss']
})
export class AddLoadsComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = false;
  public form: FormGroup;
  private submitted = false;
  private subscriptions: Subscription[] = [];
  public isEditMode: boolean = false;
  private encryptedAllocationId: string = null;
  public formDependencyData: FormDependencyData = {
    destinations: [],
    yards: [],
    tos: [],
    statuses: [],
    maxDate: new Date(),
  };
  public cardTitle: string = 'Add Loads';

  constructor(
    private formBuilder: FormBuilder,
    private allocationsService: AllocationsService,
    private destinationsService: DestinationsService,
    private yardsService: YardsService,
    private snackBar: SnackbarService,
    private route: ActivatedRoute,
    private titleService: Title
  ) { }

  ngOnInit() {
    this.subscriptions.push(this.route.params.subscribe(params => {
      this.isEditMode = !!params.id;

      if (this.isEditMode) {
        this.titleService.setTitle('Edit load');
        this.cardTitle = 'Edit Load';
        try {
          this.encryptedAllocationId = params.id;
        } catch (error) { this.isEditMode = false; noop(); }
      } else {
        this.titleService.setTitle('Add new load');
      }

      this.initForm();
    }));
    this.getFormInitialData();
  }

  private getFormInitialData() {
    this.isFormLoading = true;
    const params: DefaultListApiParams = {
      searchBy: null,
      startFrom: null,
      endTo: null,
      sortBy: 'code',
      sortDirection: 'asc',
    };
    const requests = forkJoin(
      this.destinationsService.getDestinations(params),
      this.yardsService.getYards(params),
      this.allocationsService.getAllocationStatuses(_.merge(_.clone(params), {sortBy: 'name'}))
    );

    this.subscriptions.push(requests
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse[]) => {
        this.formDependencyData = {
          destinations: response[0].data.items,
          yards: response[1].data.items,
          statuses: response[2].data.items,
          tos: this.allocationsService.getAllTos(),
          maxDate: new Date()
        };

        if (this.isEditMode) {
          this.getAllocation(this.encryptedAllocationId);
        }
      }));
  }

  private initForm() {
    this.form = this.formBuilder.group({
      id: new FormControl(null),
      containerNumber: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(11),
          Validators.pattern(/^[A-Z0-9]+$/)
        ]
      }),
      destination: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      yard: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      to: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      chassisNumber: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(20),
          Validators.pattern(/^[A-Z0-9]+$/)
        ]
      }),
      sealNumber: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(10),
          Validators.pattern(/^[A-Z0-9]+$/)
        ]
      }),
      dropDate: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      allocationStatus: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      isRailBill: new FormControl(false)
    });
  }

  get f() {
    return this.form.controls;
  }

  private getAllocation(encryptedId: string) {
    this.isFormLoading = true;
    this.subscriptions.push(this.allocationsService.getAllocation(encryptedId)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        if (_.isObject(response.data)) {
          this.form.patchValue({
            id: encryptedId,
            containerNumber: response.data.container_number,
            destination: response.data.destination_id,
            yard: response.data.yard_id,
            to: response.data.to,
            chassisNumber: response.data.chassis_number,
            sealNumber: response.data.seal_number,
            dropDate: moment(response.data.drop_date, 'YYYY-MM-DD').toDate(),
            allocationStatus: response.data.allocation_status_id,
            isRailBill: response.data.is_rail_bill
          });
        }
      }));
  }

  onSubmit() {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    this.isFormLoading = true;

    this.subscriptions.push(this.allocationsService.saveAllocation(this.form.value)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        if (response) {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop()));
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
