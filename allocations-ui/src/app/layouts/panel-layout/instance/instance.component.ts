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
import { noop, Subscription } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { ActivatedRoute } from '@angular/router';
import { SnackbarService, InstanceService, ClientService } from '../../../_services';
import { Title } from '@angular/platform-browser';
import { DefaultApiResponse, DefaultListApiParams } from '../../../_models';
import * as _ from 'lodash';

declare interface InstancetData {
  name: string;
  ipAddress: string;
  port: string;
  username: string;
  password: string;
  wifiDefaultPassword: string;
  lanIpAddress: string;
  dnsIpAddress: string;
  dnsPort: string;
  destinationUrl: string;
}

@Component({
  selector: 'app-instance',
  templateUrl: './instance.component.html',
  styleUrls: ['./instance.component.scss']
})
export class InstanceComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = true;
  public form: FormGroup;
  private submitted = false;
  private subscriptions: Subscription[] = [];
  public isEditMode: boolean = false;
  private instanceId: number = null;
  private encryptedInstanceId: string = null;
  public instanceData: InstancetData;
  public cardTitle: string = 'Add New Instance';
  public clients: object[] = [];

  constructor(
    private formBuilder: FormBuilder,
    private clientService: ClientService,
    private instanceService: InstanceService,
    private route: ActivatedRoute,
    private snackBar: SnackbarService,
    private titleService: Title) { }

  ngOnInit(): void {
    this.titleService.setTitle('Instance');
    this.initForm();

    this.subscriptions.push(this.route.params.subscribe(params => {
      this.isEditMode = !!params.id;

      if (this.isEditMode) {
        try {
          this.instanceId = _.toNumber(atob(params.id));
          this.encryptedInstanceId = params.id;
          this.getInstance(params.id);
        } catch (error) { this.isEditMode = false; noop(); }
      } else {
        this.getClients();
      }
    }));
  }

  private getClients() {
    const params: DefaultListApiParams = {
      searchBy: null,
      startFrom: null,
      endTo: null,
      sortBy: 'business_name',
      sortDirection: 'asc'
    };

    this.clientService.getClients(params)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => this.clients = response.data.items);
  }

  private getInstance(encryptedId: string) {
    this.isFormLoading = true;
    this.subscriptions.push(this.instanceService.getInstance(encryptedId)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        if (_.isObject(response.data)) {
          this.instanceData = response.data;
          this.form.patchValue({
            name: this.instanceData.name,
            ipAddress: this.instanceData.ipAddress,
            port: this.instanceData.port,
            username: this.instanceData.username,
            password: atob(this.instanceData.password),
            wifiDefaultPassword:
              !_.isNull(this.instanceData.wifiDefaultPassword) ? atob(this.instanceData.wifiDefaultPassword) : '',
            lanIpAddress: this.instanceData.lanIpAddress,
            dnsIpAddress: this.instanceData.dnsIpAddress,
            dnsPort: this.instanceData.dnsPort,
            destinationUrl: this.instanceData.destinationUrl
          });

          // remove form controls if edit mode
          this.form.removeControl('client');

          this.cardTitle = `Edit Instance - ${this.instanceData.ipAddress}`;
        }
      }));
  }

  private initForm() {
    this.form = this.formBuilder.group({
      client: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      name: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.minLength(3),
          Validators.pattern(/^[ a-zA-Z0-9]+$/)
        ]
      }),
      ipAddress: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.pattern(/^([0-9]{1,3})[.]([0-9]{1,3})[.]([0-9]{1,3})[.]([0-9]{1,3})$/)
        ]
      }),
      port: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(10)
        ]
      }),
      username: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.minLength(4),
          Validators.pattern(/^\S*$/)
        ]
      }),
      password: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(15),
          Validators.minLength(4),
          Validators.pattern(/^\S*$/)
        ]
      }),
      wifiDefaultPassword: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(15),
          Validators.minLength(5),
          Validators.pattern(/^\S*$/)
        ]
      }),
      lanIpAddress: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.pattern(/^([0-9]{1,3})[.]([0-9]{1,3})[.]([0-9]{1,3})[.]([0-9]{1,3})$/)
        ]
      }),
      dnsIpAddress: new FormControl(null),
      dnsPort: new FormControl(null),
      destinationUrl: new FormControl()
    });
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    this.isFormLoading = true;

    let promise = null;
    this.form.value.password = btoa(this.form.value.password);
    this.form.value.wifiDefaultPassword = btoa(this.form.value.wifiDefaultPassword);

    if (!this.isEditMode) {
      promise = this.instanceService.saveInstance(this.form.value);
    } else {
      promise = this.instanceService.updateInstance(this.form.value, this.encryptedInstanceId);
    }

    this.subscriptions.push(promise.pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: any) => {
        if (response) {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop()));
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}

